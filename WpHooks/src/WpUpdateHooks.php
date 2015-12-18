<?php
namespace WpHooks;
use Composer\Script\Event;

class WpUpdateHooks
{
    public static function updateWpConfig(Event $event)
    {
        if(file_exists(__DIR__.'/../../html/wp-config.php')) return;
        $handle = fopen(__DIR__.'/../../html/wp-config.php', 'w');
        fwrite($handle, "<?php require_once __DIR__.'/../wp-config.php';");
        fclose($handle);
        self::deleteTree(__DIR__.'/../../wordpress-temp/wp-content');
        self::recursiveCopy(__DIR__.'/../../wordpress-temp', __DIR__.'/../../html');
        self::deleteTree(__DIR__.'/../../wordpress-temp');
       
        if (file_exists(__DIR__.'/../../html/composer.json')) {
            unlink(__DIR__.'/../../html/composer.json');
        }
        if (file_exists(__DIR__.'/../../html/wp-config-sample.php')) {
            unlink(__DIR__.'/../../html/wp-config-sample.php');
        }
        $event->getIO()->write(
            join('', array('WordPress has been installed and "wp-config.php" ',
                'in the folder "html" has been updated to ",
                "point to the non-public root "wp-config.php" file.'
            ))
        );

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