'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    browserify = require('gulp-browserify');

var sassConfig = {
	inputDirectory: './css/sass/*.scss',
	outputDirectory: './css/',
	options: {
		outputStyle: 'expanded'
	}
}

gulp.task('seo-scripts', function() {
    gulp.src('js/src/yseo.js')
        .pipe(browserify({
          insertGlobals : true     
        }))
        .pipe(gulp.dest('./js'))
});

gulp.task('build-css', function() {
	return gulp
		.src(sassConfig.inputDirectory)
		.pipe(sass(sassConfig.options).on('error', sass.logError))
		.pipe(gulp.dest(sassConfig.outputDirectory));
});

gulp.task('watch', function() {
	gulp.watch('css/sass/*.scss', ['build-css']);
});


gulp.task('default', ['seo-scripts', 'build-css']);