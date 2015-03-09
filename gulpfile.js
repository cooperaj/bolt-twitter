var gulp = require('gulp'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    plugins = gulpLoadPlugins();

var src = {
    js: 'src/js/**/*.js',
    scss: 'src/scss/**/*.scss'
}

gulp.task('sass', function() {
    return gulp.src(src.scss)
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.sass({ 
            onError: function (err) {
                console.error('Error!', err.message);
            },
            outputStyle: 'compressed',
            includePaths: []
        }))
        .pipe(plugins.sourcemaps.write('./maps'))
        .pipe(gulp.dest('./assets/css'));
});

gulp.task('watch', ['sass'], function () {
   gulp.watch(src.scss, ['sass']);
});

gulp.task('default', ['watch']);
