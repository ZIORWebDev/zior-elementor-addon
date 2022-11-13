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
					'!build',
					'!yarn.lock'
				],
				dest: 'release/'
			}
		},
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>.<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
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
		clean: {
			build: ['build'],
			release: ['release']
		},
		git_changelog: {
			extended: {
				options: {
					app_name : '<%= pkg.title %> Changelog',
					file : 'changelog.md',
					grep_commits: '^fix|^feat|^docs|^refactor|^chore|BREAKING|^updated|^adjusted',
					tag : false
				}
			}
		},
		wp_deploy: {
			deploy: { 
				options: {
					plugin_slug: '<%= pkg.name %>',
					plugin_main_file: 'zr-elementor.php',
					svn_url: 'https://plugins.svn.wordpress.org/zr-elementor-addon',
					svn_user: 'reygcalantaol',
					build_dir: 'release/', //relative path to your build directory
					tmp_dir: 'build/'
				},
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-text-replace');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-git');
	grunt.loadNpmTasks('git-changelog');
	grunt.loadNpmTasks('grunt-wp-deploy');

	grunt.registerTask('test', ['jshint']);
	grunt.registerTask('css', ['cssmin']);
	grunt.registerTask('js', ['uglify']);
	grunt.registerTask('default', ['js', 'css']);
	grunt.registerTask('version_number', ['replace:readme', 'replace:php']);
	grunt.registerTask('do_changelog', ['git_changelog']);
	grunt.registerTask('pre_vcs', ['version_number']);
	grunt.registerTask('do_git', ['gitcommit', 'gittag', 'gitpush']);
	grunt.registerTask('do_svn', ['clean', 'copy', 'wp_deploy']);
	grunt.registerTask('release', ['default', 'pre_vcs', 'do_svn', 'do_git']);
};