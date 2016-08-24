# Taco 2016 Boilerplate Documentation

This boilerplate does not include WordPress. Instead it uses composer to install the latest version of WordPress and the dependencies in the the taco-theme folder. The intention is to keep WordPress out of project repositories so it can auto updated independently of the wp-content directory.

### Installation
Run "composer install" from the html folder's parent. If successful, WordPress will be installed in the "html" directory without affecting the "wp-content" folder. A post install script will also be ran to install vendor files in the taco-theme/app/core. These files are vital for the theme to function properly.

The contents of wp-config.php in the html directory will be replaced with `<?php require_once __DIR__.'/../wp-config.php';` so that it points to another wp-config.php file outside of the public root. This is where the actual wordpress configuration settings need to be setup.  There is a sample wp-config.php.sample included with the boilerplate.  

After the above is setup, you can access [yoursite.dev]/wp-admin as normal to continue with the WordPress installation.

In `AppOption::getSingular()`, change "App Option" to "[Client Name] Option".

Run `init` from the Taco theme directory to set up your Git hooks.

### Webpack
Run `webpack -d --watch` from the theme directory to create development builds and watch your Javascript and SASS files.

Run `webpack -p` from the theme directory to do a production build.

##### Sources
Source files are located in the `src` directory in the Taco theme directory, and compiled files and sourcemaps are located in the `dist` folder.

All Javascript files in the top level `src/js` folder are built and output to the `dist` folder.  Any Javascript meant to be included by these top level files should go under subdirectories in the `src/js` folder.  The top level Javascript files are responsible for importing any SASS files that need to be built and output (see `src/js/main.js` for an example).

SASS files similarly live in the `src/scss` directory.  These files are not auto compiled until they're included by a Javascript file, so it's not entirely necessary to enforce a naming convention for these files, but in general, included files should begin with an underscore and top level files should not.

At this time, CSS is also output to the `dist` directory and not output by the Javascript itself, so you do need to explicitly include them in your HTML.

### Deploying
Until a site is live, development can happen on the `master` branch which can be auto-deployed to both the staging and production server.  Once it's launched however, development should be switched to the `develop` branch which is auto-deployed to the staging server.  The production server should run off the `master` branch and have deployment set to manual.


##### Other Documentation
If you're looking for documentation on TacoForms that was previously included in this boilerplate, it has moved.
It is now in a seperate repo at [https://github.com/tacowordpress/mr-spicy](https://github.com/tacowordpress/mr-spicy).
