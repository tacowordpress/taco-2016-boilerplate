/*
 * Vermilion Gruntfile
 * http://vermilion.com
 */
 
 
/**
 * Grunt Module
 */
module.exports = function(grunt) {
 
'use strict';
// Project configuration.
grunt.initConfig({
  pkg: grunt.file.readJSON('package.json'),
  sass: {
    dist: {
      options: {
        style: 'compressed',
        sourcemap: 'none'
      },
      files: {
        '_/css/main.css' : '_/scss/main.scss'
      }
    },
    dev: {
      options: {
        style: 'compact',
        sourcemap: 'inline'
      },
      files: {
        '_/css/main-dev.css': '_/scss/main.scss'
      }
    }
  },
  
  uglify: {
    options: {
      mangle: {
        except: ['$']
      },
      semicolons: true
    },
    files: {
      src: '_/js/modules/*.js',  // source files mask
      dest: '_/js/min/',    // destination folder
      expand: true,    // allow dynamic building
      flatten: true,   // remove all unnecessary nesting
      ext: '.min.js'   // replace .js to .min.js
    }
  },
    
  watch: {
    gruntfile: {
      files: ['gruntfile.js']
    },
    styles: {
      files: ['_/scss/*.scss'],
      tasks: ['sass:dist', 'sass:dev']
    },
    uglification: {
      files: ['_/js/*.js', '_/js/modules/*.js'],
      tasks: ['uglify']
    }
  }
});
 
// load plugins here
grunt.loadNpmTasks('grunt-contrib-sass');
grunt.loadNpmTasks('grunt-contrib-uglify');
grunt.loadNpmTasks('grunt-contrib-watch');

 
// Default task(s).
grunt.registerTask('default', ['sass:dist', 'sass:dev']);
grunt.registerTask('dev', ['watch']);
 
};