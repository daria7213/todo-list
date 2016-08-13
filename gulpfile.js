var gulp = require('gulp'), sass = require('gulp-sass');

var
    source = 'web/src/',
    dest = 'web/dist/';

var bootstrapSass = {
    in: './node_modules/bootstrap-sass/'
};

var fontAwesome = {
    in: './node_modules/font-awesome/'
};

var fonts = {
    in: [source + 'fonts/*.*', bootstrapSass.in + 'assets/fonts/**/*', fontAwesome.in + 'fonts/*'],
    out: dest + 'fonts/'
};

var scss = {
    in: [source + 'scss/main.scss', fontAwesome.in + 'css/font-awesome.min.css'],
    out: dest + 'css/',
    watch: source + 'scss/**/*',
    sassOpts: {
        outputStyle: 'nested',
        precision: 3,
        errLogToConsole: true,
        includePaths: [bootstrapSass.in + 'assets/stylesheets']
    }
};

gulp.task('fonts', function () {
    return gulp
        .src(fonts.in)
        .pipe(gulp.dest(fonts.out));
});

gulp.task('sass', function (){
    return gulp.src(scss.in)
        .pipe(sass(scss.sassOpts))
        .pipe(gulp.dest(scss.out));
});

gulp.task('default', ['sass'], function () {
    gulp.watch(scss.watch, ['sass']);
});