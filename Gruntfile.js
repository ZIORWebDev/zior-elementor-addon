module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			all: {
				options: {
					banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
						' * <%= pkg.homepage %>\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
						' * Licensed GPLv2+' +
						' */\n'
				},
				files: {
					'assets/js/main.min.js': ['assets/js/src/main.js'],
					'assets/js/posts-filter.min.js': ['assets/js/src/posts-filter.js'],
					'assets/js/slider.min.js': ['assets/js/src/slider.js']
				}
			}
		},
		cssmin: {
			options: {
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			minify: {
				expand: true,
				cwd: 'assets/css/src',
				src: ['*.css', '!*.min.css'],
				dest: 'assets/css/',
				ext: '.min.css'
			}
		},
		jshint: {
			all: [
				'Gruntfile.js',
				'assets/js/src/**/*.js'
			],
			options: {
				curly:   true,
				eqeqeq:  true,
				immed:   true,
				latedef: true,
				newcap:  true,
				noarg:   true,
				sub:     true,
				undef:   true,
				boss:    true,
				eqnull:  true,
				esversion: 6,
				globals: {
					jQuery: true,
					module: true,
					window: true,
					elementorFrontend: true,
					Swiper: true
				}
			}
		},
		watch: {
			css: {
				files: ['assets/css/**/*.css'],
				tasks: ['css'],
				options: {
					debounceDelay: 500
				}
			},
			scripts: {
				files: ['assets/js/src/**/*.js'],
				tasks: ['test', 'js'],
				options: {
					debounceDelay: 500
				}
			}
		},
		copy: {
			main: {
				src:  [
					'**',
					'!node_modules/**',
					'!release/**',
					'!.git/**',
					'!css/src/**',
					'!js/src/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.github',
					'!README.md',
					'!yarn.lock'
				],
				dest: 'release/<%= pkg.version %>/'
			}
		},
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/zr-elementor-addon.<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: ['**/*'],
				dest: 'zr-elementor-addon/'
			}
		},
		replace: {
			readme: {
				src: ['readme.txt'],
				overwrite: true,
				replacements: [{
					from: /Stable tag: (.*)/,
					to: "Stable tag: <%= pkg.version %>"
				}]
			},
			php: {
				src: ['zr-elementor.php'],
				overwrite: true,
				replacements: [{
					from: /Version:\s*(.*)/,
					to: "Version: <%= pkg.version %>"
				}, {
					from: /protected \$version = \s*'(.*)'\s*;/,
					to: "protected $version = '<%= pkg.version %>';"
				}]
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-text-replace');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-clean');

	//TODO CLEAN
	grunt.registerTask('test', ['jshint']);
	grunt.registerTask( 'css', ['cssmin'] );
	grunt.registerTask( 'js', ['uglify'] );
	grunt.registerTask( 'default', ['test', 'js', 'css'] );
};