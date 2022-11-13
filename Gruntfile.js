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
					debounceDelay: 250
				}
			},
			scripts: {
				files: ['assets/js/src/**/*.js'],
				tasks: ['test', 'js'],
				options: {
					debounceDelay: 250
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
					'!assets/css/src/**',
					'!assets/js/src/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.github',
					'!README.md',
					'!yarn.lock'
				],
				dest: 'release/<%= pkg.version %>/'
			},
			svn_trunk: {
				cwd: 'release/<%= pkg.version %>',
				src:  [
					'**/*',
				],
				dest: 'build/<%= pkg.name %>/trunk/'
			},
			svn_tag: {
				cwd: 'release/<%= pkg.version %>',
				src:  [
					'**/*',
				],
				dest: 'build/<%= pkg.name %>/tags/<%= pkg.version %>/'
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
		},
		gittag: {
			addtag: {
				options: {
					tag: '<%= pkg.version %>',
					message: 'Version <%= pkg.version %>'
				}
			}
		 },
		 gitcommit: {
			 commit: {
				 options: {
					 message: 'Version <%= pkg.version %>',
					 noVerify: true,
					 noStatus: false,
					 allowEmpty: true
				 },
				 files: {
					 src: [ 'readme.txt', 'zr-elementor.php', 'package.json' ]
				 }
			 }
		 },
		 gitpush: {
			 push: {
				 options: {
					 tags: true,
					 remote: 'origin',
					 branch: 'origin/main'
				 }
			 }
		 },
		 svn_checkout: {
			make_local: {
				repos: [
					{
						path: [ 'build' ],
						repo: 'http://plugins.svn.wordpress.org/zr-elementor-addon'
					}
				]
			}
			},
		 push_svn: {
			 options: {
				 remove: true
			 },
			 main: {
				 src: 'build/<%= pkg.name %>',
				 dest: 'http://plugins.svn.wordpress.org/zr-elementor-addon',
				 tmp: 'build/make_svn'
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
	grunt.loadNpmTasks('grunt-git');
	grunt.loadNpmTasks('grunt-svn-checkout');
	grunt.loadNpmTasks('grunt-push-svn');

	grunt.registerTask('test', ['jshint']);
	grunt.registerTask('css', ['cssmin']);
	grunt.registerTask('js', ['uglify']);
	grunt.registerTask('default', ['js', 'css']);
	grunt.registerTask('version_number', ['replace:readme', 'replace:php']);
	grunt.registerTask('pre_vcs', ['version_number']);
	grunt.registerTask('do_git', ['gitcommit', 'gittag', 'gitpush']);
	grunt.registerTask('do_svn', ['svn_checkout', 'copy:main', 'copy:svn_trunk', 'copy:svn_tag', 'push_svn']);
	grunt.registerTask('release', ['default', 'pre_vcs', 'do_svn', 'do_git']);
};