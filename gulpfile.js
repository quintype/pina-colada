var gulp = require("gulp");
var sass = require("gulp-sass");
var rev = require('gulp-rev');
var multistream = require("multistream");
var util = require("gulp-util");
var minify = require("gulp-minify");
var sourcemaps = require("gulp-sourcemaps");

var production = !!util.env.production;

var elixir = require('laravel-elixir');
var shell = require("gulp-shell")
var del = require("del");

var destination = "public/pina-colada/assets";
var tempPath = "tmp/asset";

elixir.config.publicPath = destination;

gulp.task("delete-public", function() {
  return del([destination]);
});

gulp.task("compile", function() {
  var css = gulp.src("./resources/assets/sass/application.scss")
                .pipe(production ? util.noop() : sourcemaps.init())
                .pipe(sass({
                  outputStyle: production ? 'compressed' : 'nested'
                 }).on("error", sass.logError))
                .pipe(production ? util.noop() : sourcemaps.write());
  var images = gulp.src("./resources/assets/images/**/*");
  return css
           .pipe(rev())
           .pipe(gulp.dest("./foo"))
           .pipe(rev.manifest())
           .pipe(gulp.dest("./resources"));
});

elixir(function(mix) {
    mix.task("delete-public");
});

elixir(function(mix) {
    mix.sass('application.scss', tempPath + "/application.css");
});

elixir(function(mix) {
    mix.browserify("application.js", tempPath + "/application.js");
});

elixir(function(mix) {
    mix.copy("resources/assets/images", tempPath);
});

elixir(function(mix) {
    mix.copy(tempPath, destination);
});

elixir(function(mix) {
    mix.version([
      "application.js",
      "application.css",
      "**/*.gif",
      "**/*.jpg",
      "**/*.png",
      "**/*.svg"
    ], destination);
});

