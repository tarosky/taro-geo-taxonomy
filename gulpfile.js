var gulp = require('gulp'),
    $ = require('gulp-load-plugins')(),
    pngquant = require('imagemin-pngquant');


// Sass's task
gulp.task('sass',function(){

    return gulp.src(['./assets/sass/**/*.scss'])
        .pipe($.plumber())
        .pipe($.sourcemaps.init())
        .pipe($.sass({
            errLogToConsole: true,
            outputStyle: 'compressed',
            sourceComments: 'normal',
            sourcemap: true,
            includePaths: [
                './assets/sass'
            ]
        }))
        .pipe($.sourcemaps.write('./map'))
        .pipe(gulp.dest('./assets/css'));
});


// Minify
gulp.task('js', function(){
    return gulp.src(['./assets/js/src/**/*.js'])
        .pipe($.sourcemaps.init({
            loadMaps: true
        }))
        .pipe($.uglify())
        .on('error', $.util.log)
        .pipe($.sourcemaps.write('./map'))
        .pipe(gulp.dest('./assets/js/dist/'));
});

// JS Hint
gulp.task('jshint', function(){
    return gulp.src(['./assets/js/src/**/*.js'])
        .pipe($.jshint('./assets/.jshintrc'))
        .pipe($.jshint.reporter('jshint-stylish'));
});
//
//// Image min
//gulp.task('imagemin', function(){
//    return gulp.src('./assets/img/src/**/*')
//        .pipe($.imagemin({
//            progressive: true,
//            svgoPlugins: [{removeViewBox: false}],
//            use: [pngquant()]
//        }))
//        .pipe(gulp.dest('./assets/img'));
//});

// Copy JS
gulp.task('copyJs', function(){
    return gulp.src(['./bower_components/jquery-tokeninput/src/jquery.tokeninput.js'])
        .pipe($.uglify())
        .pipe(gulp.dest('./assets/js/dist/'));
});

// watch
gulp.task('watch',function(){
    gulp.watch('./assets/sass/**/*.scss',['sass']);
    gulp.watch('./assets/js/src/**/*.js',['js', 'jshint']);
    //gulp.watch('./assets/img/src/**/*',['imagemin']);
});

// Build
gulp.task('build', ['jshint', 'js', 'sass']);

// Default Tasks
gulp.task('default', ['watch']);
