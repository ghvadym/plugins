const
    {src, dest, watch, series} = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    cleanCss = require('gulp-clean-css'),
    sourceMaps = require('gulp-sourcemaps');

const
    SRC   = 'scss/app.scss',
    DEST  = '../assets/css/',
    WATCH = 'scss/**/*.scss';

function scss()
{
    return src(SRC)
        .pipe(sourceMaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer(['last 2 versions'], {cascade: true}))
        .pipe(cleanCss())
        .pipe(sourceMaps.write())
        .pipe(dest(DEST));
}

function watcher()
{
    watch(WATCH, scss);
}

exports.default = series(scss, watcher);
