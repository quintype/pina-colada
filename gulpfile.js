var gulp = require('gulp');
var sass = require('gulp-sass');
var rev = require('gulp-rev');
var revReplace = require('gulp-rev-replace');
var merge = require('merge-stream');
var util = require('gulp-util');
var minify = require('gulp-minify');
var sourcemaps = require('gulp-sourcemaps');
var browserify = require('browserify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var uglify = require('gulp-uglify');
var del = require('del');

var destination = "public/pina-colada/assets";

function compileAll() {
  var production = !!util.env.production;
  var css = gulp.src("./resources/assets/sass/application.scss")
                .pipe(production ? util.noop() : sourcemaps.init())
                .pipe(sass({
                  outputStyle: production ? 'compressed' : 'nested'
                 }).on("error", sass.logError))
                .pipe(production ? util.noop() : sourcemaps.write());

  var images = gulp.src("./resources/assets/images/**/*");

  var js = browserify({
      entries: "./resources/assets/js/application.js"
    }).bundle()
    .pipe(source("application.js"))
    .pipe(buffer())
    .pipe(production ? util.noop() : sourcemaps.init())
    .pipe(production ? uglify() : util.noop())
    .pipe(production ? util.noop() : sourcemaps.write());

  return merge(css, js, images)
           .pipe(rev())
           .pipe(revReplace())
           .pipe(gulp.dest(destination))
           .pipe(rev.manifest())
           .pipe(gulp.dest(destination));
};

gulp.task("clean", function() {
  return del([destination]);
});

gulp.task("compile", compileAll);
gulp.task("clean-compile", ["clean"], compileAll);
gulp.task("default", ["clean-compile"]);

gulp.task('watch', ['clean-compile'], function() {
  gulp.watch("./resources/assets/**/*", ['compile']);
});
