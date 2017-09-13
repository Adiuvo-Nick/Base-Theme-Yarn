// Load plugins
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    rename = require('gulp-rename'),
    notify = require('gulp-notify'),
    cleanCSS = require('gulp-clean-css'),
    mainBowerFiles = require('main-bower-files')
uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    gulpFilter = require('gulp-filter'),
    browserSync = require('browser-sync').create(),
    order = require('gulp-order')
;

const imagemin = require('gulp-imagemin');

// Static Server + watching scss/html files
gulp.task('serve', ['sass', 'js', 'css', 'img', 'fonts'], function () {
    browserSync.init({
        proxy: "localhost:8187",
        online: true
    });

    gulp.watch('./assets/scss/**/*.scss', ['sass']);
    gulp.watch('./assets/css/*.css', ['css']);
    gulp.watch('./assets/js/*.js', ['js']);
    gulp.watch('./assets/img/**/*', ['img']);
    gulp.watch('./assets/fonts/**/*', ['fonts']);
    gulp.watch('./**/*.php').on('change', browserSync.reload);
});

// Gulp
gulp.task('sass', function () {
    return gulp.src('assets/scss/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('assets/css'));
});

// IMG
gulp.task('img', function () {
    return gulp.src('assets/img/**/*')
        .pipe(imagemin())
        .pipe(gulp.dest('assets/dist/img'))
        .pipe(browserSync.stream());
});

// CSS
gulp.task('css', function () {
    return gulp.src('assets/css/*.css')
        .pipe(cleanCSS())
        .pipe(concat("app.min.css"))
        .pipe(gulp.dest('./assets/dist/css'))
        .pipe(browserSync.stream());
});

// JS
gulp.task('js', function () {
    return gulp.src('assets/js/*.js')
        .pipe(uglify())
        .pipe(concat("app.min.js"))
        .pipe(gulp.dest('./assets/dist/js'))
        .pipe(browserSync.stream());
});

// Fonts
gulp.task('fonts', function () {
    return gulp.src([
        'assets/fonts/**/*'])
        .pipe(gulp.dest('assets/dist/fonts/'));
});

// Bower JS
gulp.task('bower', function () {
    gulp.src(mainBowerFiles('**/*.css', {debugging: true}))
        .pipe(cleanCSS())
        .pipe(concat("vendor.min.css"))
        .pipe(gulp.dest("./assets/dist/vendor"))
        .pipe(browserSync.stream());

    gulp.src(mainBowerFiles('**/*.js', {debugging: true}))
        .pipe(uglify())
        .pipe(concat("vendor.min.js"))
        .pipe(gulp.dest("./assets/dist/vendor"))
        .pipe(browserSync.stream());
});

gulp.task('default', ['bower', 'serve']);
gulp.task('build', ['bower', 'sass', 'js', 'css', 'img', 'fonts']);
