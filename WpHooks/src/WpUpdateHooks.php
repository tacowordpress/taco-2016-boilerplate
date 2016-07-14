<?php
namespace WpHooks;
use Composer\Script\Event;

/* scenarios
 * cloned from github - no prior composer installs or updates - user does composer install
 * all wordpress files and boilerplate files are there - user does comopser install or update
 * files are on staging or production freshly deployed from service - user does an install or update
 */

class WpUpdateHooks
{
    public static $boilerplatePreviouslyInstalled = false;
    public static $wordPressAlreadyInstalled = false;

    public static function preAnything(Event $event) {
        // is WordPress setup with all files in place?
        if(file_exists(__DIR__.'/../../html') && file_exists(__DIR__.'/../../html/wp-admin')) {
            self::$wordPressAlreadyInstalled = true;
            $event->getIO()->write('WordPress already installed');
            $event->stopPropagation();
            return;
        }
        // boilerplate setup - no wp files? must have been deployed to another server
        if(file_exists(__DIR__.'/../../html') && !file_exists(__DIR__.'/../../html/wp-admin')) {
            self::$boilerplatePreviouslyInstalled = true;
            // we should install wp files
            self::moveCustomFiles($event);
            $event->getIO()->write('The Boilerplate was already setup, but there are no wp files. Installing WordPress.');
            return;
        }
        $event->getIO()->write('Nothing to do in pre-install or pre-update. continuing...');
        return;
    }

    public static function postAnything(Event $event) {
        if(self::$wordPressAlreadyInstalled === true) {
            $event->getIO()->write('...done');
            return;
        }
        if(self::$boilerplatePreviouslyInstalled === true) {
            self::doBoilerplateAlreadySetupScript($event);
            return;
        }
        // must be a fresh install
        self::doFreshInstall($event);
        return;
    }

    public static function doFreshInstall(Event $event)
    {
        self::updateWpConfig($event);
        self::installComposerInTheme($event);
        self::printRemainingInstructions($event);
    }

    public static function updateWpConfig(Event $event)
    {
        if(!file_exists(__DIR__.'/../../html')) {
            $event->getIO()->write('Please run "composer update" instead of "install"');
            $event->stopPropagation();
            return;
        }
        self::deleteTreeWithSymlinks(__DIR__.'/../../html/wp-content');
        self::recursiveCopy(__DIR__.'/../../custom-wordpress-temp/wp-content', __DIR__.'/../../html/wp-content');
        copy(__DIR__.'/../../custom-wordpress-temp/.htaccess', __DIR__.'/../../html/.htaccess');
        self::deleteTree(__DIR__.'/../../custom-wordpress-temp');

        if (file_exists($wp_config = __DIR__.'/../../wp-config.php')) {
            copy(__DIR__.'/wp-config.php', __DIR__.'/../../wp-config.php');
        }

        $handle = fopen(__DIR__.'/../../html/wp-config.php', 'w');
        fwrite($handle, "<?php require_once __DIR__.'/../wp-config.php';");
        fclose($handle);

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

    public static function moveCustomFiles(Event $event)
    {
        $event->getIO()->write('Moving files to safety before installing WordPress...');
        if(!file_exists($wp_temp = __DIR__.'/../../wp-temp')) {
            mkdir($wp_temp);
        }
        if(file_exists($wp_content = __DIR__.'/../../html/wp-content')) {
            rename($wp_content, __DIR__.'/../../wp-temp/wp-content');
        }
        if(file_exists($htaccess = __DIR__.'/../../html/.htaccess')) {
            rename($htaccess, __DIR__.'/../../wp-temp/.htaccess');
        }
        if(file_exists($html = __DIR__.'/../../html')) {
            self::deleteTreeWithSymlinks($html);
        }
        if(file_exists($wp_temp = __DIR__.'/../../wp-temp')) {
            self::deleteTreeWithSymlinks($wp_temp);
        }
        $event->getIO()->write('...done');
    }

    public static function doBoilerplateAlreadySetupScript(Event $event)
    {
        $event->getIO()->write('The boilerplate was previously setup.');

        if(!file_exists(__DIR__.'/../../html')) {
            $event->getIO()->write('Please run "composer update" instead of "install"');
            $event->stopPropagation();
            return;
        }

        if (file_exists($wp_config = __DIR__.'/../../wp-config.php')) {
            copy(__DIR__.'/wp-config.php', __DIR__.'/../../wp-config.php');
        }
        
        if(!file_exists($wp_config = __DIR__.'/../../html/wp-config.php')) {
            $handle = fopen($wp_config, 'w');
            fwrite($handle, "<?php require_once __DIR__.'/../wp-config.php';");
            fclose($handle);
        }

        if(file_exists($wp_content_dir = __DIR__.'/../../html/wp-content')) {
            self::deleteTreeWithSymlinks($wp_content_dir);
        }

        if(file_exists($temp_folder = __DIR__.'/../../wp-temp')) {
            rename($temp_folder.'/.htaccess', __DIR__.'/../../html/.htaccess');
            rename($temp_folder.'/wp-content', __DIR__.'/../../html/wp-content');
        }

        if (file_exists($composer_dir = __DIR__.'/../../html/composer.json')) {
            unlink($composer_dir);
        }
        if (file_exists($wp_config_sample = __DIR__.'/../../html/wp-config-sample.php')) {
            unlink($wp_config_sample );
        }
        return;
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

    public static function symlinkExists($path)
    {
        if(!file_exists($path)) {
            return false;
        }
        if(!is_link($path)) {
            return false;
        }
        return true;
    }

    public static function printRemainingInstructions()
    {
        if(self::$boilerplatePreviouslyInstalled === true) {
            echo 'All done!';
            return;
        }
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
        if(file_exists(__DIR__.'/../../README.md')) {
            rename(__DIR__.'/../../README.md', __DIR__.'/../../boilerplate-readme.md');
        }
        if(!self::symlinkExists($link = __DIR__.'/../../shortcut-taco-theme')) {
            symlink(__DIR__.'/../../html/wp-content/themes/taco-theme', $link);
        }
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
        if(is_link($dir)) {
            return unlink(readlink($dir));
        }
        return rmdir($dir);
    }


    public static function deleteTreeWithSymlinks($dir)
    {
        if (is_link($dir)) {
            unlink($dir);
        } elseif (!file_exists($dir)) {
            return;
        } elseif (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file != '.' && $file != '..') {
                    self::deleteTreeWithSymlinks("$dir/$file");
                }
            }
            rmdir($dir);
        } elseif (is_file($dir)) {
            unlink($dir);
        } else {
            echo "WARNING: Cannot delete $dir (unknown file type)\n";
        }
    }
}
