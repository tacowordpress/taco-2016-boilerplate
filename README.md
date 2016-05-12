# Taco 2016 Boilerplate Documentation

This boilerplate does not include WordPress. Instead it uses composer to install the latest version of WordPress and the dependencies in the the taco-theme folder. The intention is to keep WordPress out of project repositories so it can auto updated independently of the wp-content directory.

### Installation
Run "composer install" from the html folder's parent. If successful, WordPress will be installed in the "html" directory without affecting the "wp-content" folder. A post install script will also be ran to install vendor files in the taco-theme/app/core. These files are vital for the theme to function properly.

The contents of wp-config.php in the html directory will be replaced with `<?php require_once __DIR__.'/../wp-config.php';` so that it points to another wp-config.php file outside of the public root. This is where the actual wordpress configuration settings need to be setup.

After the above is setup, you can access [yoursite.dev]/wp-admin as normal to continue with the WordPress installation.

In `AppOption::getSingular()`, change "App Option" to "[Client Name] Option".

##### Other Documentation
If you're looking for documentation on TacoForms that was previously included in this boilerplate, it has moved.
It is now in a seperate repo at [https://github.com/tacowordpress/mr-spicy](https://github.com/tacowordpress/mr-spicy).

