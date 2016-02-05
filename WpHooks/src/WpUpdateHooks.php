<?php
namespace WpHooks;
use Composer\Script\Event;

class WpUpdateHooks
{
    public static function updateWpConfig(Event $event)
    {
        if (file_exists(__DIR__.'/../../html/wp-config.php')) return;
        $handle = fopen(__DIR__.'/../../html/wp-config.php', 'w');
        fwrite($handle, "<?php require_once __DIR__.'/../wp-config.php';");
        fclose($handle);
        self::deleteTree(__DIR__.'/../../wordpress-temp/wp-content');
        self::recursiveCopy(__DIR__.'/../../wordpress-temp', __DIR__.'/../../html');
       
        if (file_exists(__DIR__.'/../../html/composer.json')) {
            unlink(__DIR__.'/../../html/composer.json');
        }
        if (file_exists(__DIR__.'/../../html/wp-config-sample.php')) {
            unlink(__DIR__.'/../../html/wp-config-sample.php');
        }
        $event->getIO()->write(
            join('', array('WordPress has been installed and "wp-config.php" ',
                'in the folder "html" has been updated to ',
                'point to the non-public root "wp-config.php" file.'
            ))
        );

    }

    public static function installComposerInTheme()
    {
        $composer_path = `which composer`;
        if(!strlen($composer_path)) {
            $composer_path = `which composer.phar`;
        }
        if (!preg_match('/composer/', $composer_path)) {
            echo "\r\n";
            echo "This script seems to be having trouble finding your composer.phar \r\n";
            echo "Please cd into the taco-theme/app/core directory and manually run \"composer install\" \r\n";
            echo "\r\n";
        }
        $composer_path = preg_replace('/(\s+)/', '', $composer_path);
        $c = [];
        $c[] = "cd ".__DIR__."/../../html/wp-content/themes/taco-theme/app/core/ \r\n";
        $c[] = "php ".$composer_path. " install";
        exec(join('',$c));

    }


    public static function printRemainingInstructions()
    {
        echo "\r\n";
        echo "\r\n";
        echo "\r\n";
        echo "Please edit \"wp-config.php\" in the non public root by: \r\n";
        echo " • changing the database prefix \r\n";
        echo " • adding salts \r\n";
        echo " • adding your database info \r\n";
        echo "Remember: \"wp-config.php\" should not be part of the project's repository\r\n";
        echo " and should be added to your \".gitignore\" file.\r\n";
        echo 'Keep your database info somewhere for safe keeping!';
        echo "\r\n";
        echo "\r\n";

        // cleanup
        self::deleteTree(__DIR__.'/../../wordpress-temp');
        rename(__DIR__.'/../../README.md', __DIR__.'/../../boilerplate-readme.md');
        symlink(__DIR__.'/../../html/wp-content/themes/taco-theme', __DIR__.'/../../shortcut-taco-theme');
    }


    public static function recursiveCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    public static function deleteTree($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::deleteTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}