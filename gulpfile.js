/*
* Gulp Configuration File.
* 
* @since 21-March-2018
* @author Reedyseth
* @version 1.0.0
* 
* */
var gulp   = require( 'gulp' );
var eslint = require( 'gulp-eslint' );
var watch  = require('gulp-watch');
var sass   = require('gulp-sass');

gulp.task('lint', function() {
    // ESLint ignores files with "node_modules" paths.
    // So, it's best to have gulp ignore the directory as well.
    // Also, Be sure to return the stream from the task;
    // Otherwise, the task may end before the stream has finished.
    return gulp.src([
            '**/*.js',
            '!node_modules/**',
            '!src/bower_components/**',
            '!src/includes/tinymce-lite/**'
    ])
    // eslint() attaches the lint output to the "eslint" property
    // of the file object so it can be used by other modules.
        .pipe(eslint())
        // eslint.format() outputs the lint results to the console.
        // Alternatively use eslint.formatEach() (see Docs).
        .pipe(eslint.format())
        // To have the process exit with an error code (1) on
        // lint error, return the stream and pipe to failAfterError last.
        .pipe(eslint.failAfterError());
});

gulp.task('sass', function () {
    return gulp.src('src/includes/sass/*.scss')
        .pipe(sass.sync().on( 'error', sass.logError ) )
        .pipe( gulp.dest( 'src/includes/css' ) );
});

gulp.task('watch', function () {
    // gulp.watch('src/includes/js/admin/**/*.js', ['lint']);
    gulp.watch('src/includes/sass/*.scss', ['sass']);
});

gulp.task('default', ['lint'], function () {
    // This will only run if the lint task is successful...
});