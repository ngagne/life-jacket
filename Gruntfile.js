module.exports = function(grunt) {
    "use strict";
    require("matchdep").filterDev("grunt-*").forEach(grunt.loadNpmTasks);

    var publicDir = 'public'; // change this to match the new of your public directory

    var jsCommonFiles = [
        'bower_components/jquery/dist/jquery.js',
        'bower_components/foundation-sites/dist/foundation.js'
    ];

    var jsAppFiles = [
        'src/js/app.js',
        'src/js/default/common.js'
    ];

    var jsAdminFiles = [
        'src/js/admin.js'
    ];

    var gruntConfig = {
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            options: {
                outputStyle: 'compressed',
                includePaths: [
                    'bower_components/foundation-sites/scss'
                ]
            },
            app: {
                files: {}
            },
            admin: {
                files: {}
            }
        },

        concat: {
            options: {
                separator: ';'
            },
            app: {
                src:    jsCommonFiles.concat(jsAppFiles),
                dest:   publicDir + '/js/app.min.js'
            },
            admin: {
                src:    jsCommonFiles.concat(jsAdminFiles),
                dest:   publicDir + '/js/admin.min.js'
            }
        },

        uglify: {
            options: {
                compress: {
                    sequences:      true,
                    drop_console:   true,
                    dead_code:      true,
                    unused:         true,
                    join_vars:      true,
                    unsafe:         true
                }
            },
            app: {
                files: {
                    '<%=concat.app.dest %>': ['<%=concat.app.dest %>']
                }
            },
            admin: {
                files: {
                    '<%=concat.admin.dest %>': ['<%=concat.admin.dest %>']
                }
            }
        },

        watch: {
            jsApp: {
                files: jsAppFiles,
                tasks: ['js:app']
            },
            jsAdmin: {
                files: jsAdminFiles,
                tasks: ['js:admin']
            },
            sass: {
                files: ['bower_components/**/*.scss', 'src/scss/*.scss'],
                tasks: ['sass:app', 'sass:admin']
            }
        }
    };
    gruntConfig.sass.app.files[publicDir + '/css/app.min.css'] = 'src/scss/app.scss';
    gruntConfig.sass.admin.files[publicDir + '/css/admin.min.css'] = 'src/scss/admin.scss';

    // configure the tasks
    grunt.initConfig(gruntConfig);

    grunt.registerTask('default',   ['build', 'build:admin', 'watch']);
    grunt.registerTask('css', ['sass:app']);
    grunt.registerTask('css:admin', ['sass:admin']);
    grunt.registerTask('js', ['concat:app', 'uglify:app']);
    grunt.registerTask('js:admin', ['concat:admin', 'uglify:admin']);
    grunt.registerTask('build', ['css', 'js']);
    grunt.registerTask('build:admin', ['css:admin', 'js:admin']);
};