/*
 * Vermilion Gruntfile
 * http://vermilion.com
 */
 
'use strict';
 
/**
 * Grunt Module
 */
module.exports = function(grunt) {
 
// Project configuration.
grunt.initConfig({
  pkg: grunt.file.readJSON('package.json'),
  sass: {
    options: {
      sourceMap: true
    },
    dist: {
      files: {
        '_/css/main.css': '_/scss/main.scss'
      }
    }
  },
  uglify: {
    options: {
      mangle: {
        except: ['$', 'jQuery']
      },
      semicolons: true
    },
    my_target: {
      files: {
        '_/js/app.js': ['_/js/main.js']
      }
    }
  },
  jshint: {
    files: ['_/js/*.js', '!_/js/app.js'],
    options: {
      globals: {
        jquery: true,
      },
      browser: true,
      devel: true,
      jquery: true,
      elision: true,
      laxbreak: true,
      laxcomma: true,
      notypeof: true,
      bitwise: true,
      eqeqeq: true,
      freeze: true,
      newcap: true,
      noarg: false,
      noempty: true,
      nonbsp: true,
      nonew: true,
      regexp: true,
      undef: true,
      unused: true,
      predef: [
        'Modernizr'
      ]
    }
  },
    
  watch: {
    gruntfile: {
      files: ['gruntfile.js']
    },
    templates: {
      files: ['*.html', '*.php'],
      options: {
        spawn: false,
        livereload: true
      }
    },
    jshints: {
      files: ['<%= jshint.files %>'],
      tasks: ['jshint']
    },
    styles: {
      files: ['_/scss/*.scss'],
      tasks: ['sass']
    }
  }
});
 
// load plugins here
grunt.loadNpmTasks('grunt-sass');
grunt.loadNpmTasks('grunt-contrib-uglify');
grunt.loadNpmTasks('grunt-contrib-jshint');
grunt.loadNpmTasks('grunt-contrib-watch');

 
// Default task(s).
grunt.registerTask('default', ['sass:dist']);
grunt.registerTask('dev', ['watch']);
 
};