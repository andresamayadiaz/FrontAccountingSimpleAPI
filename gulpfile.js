var gulp = require('gulp');
var gutil = require('gulp-util');
var child_process = require('child_process');
var exec2 = require('child_process').exec;
var async = require('async');
var template = require('lodash.template');
var rename = require("gulp-rename");

var execute = function(command, options, callback) {
  if (options == undefined) {
    options = {};
  }
  command = template(command, options);
  if (!options.silent) {
    gutil.log(gutil.colors.green(command));
  }
  if (!options.dryRun) {
    exec2(command, function(err, stdout, stderr) {
      gutil.log(stdout);
      gutil.log(gutil.colors.yellow(stderr));
      callback(err);
    });
  } else {
    callback(null);
  }
};

var paths = {
  src: ['**/*.inc', '**/*.php', '!vendor/**'],
  testUnit: ['tests/**/*.php']
};

gulp.task('tasks', function(cb) {
  var command = 'grep gulp\.task gulpfile.js';
  execute(command, null, function(err) {
    cb(null); // Swallow the error propagation so that gulp doesn't display a nodejs backtrace.
  });
});

gulp.task('default', function() {
  // place code for your default task here
});

/*
gulp.task('env-files', function() {
  gulp.src('tests/data/*.php')
    .pipe(gulp.dest('htdocs/'));
  gulp.src('tests/data/lang/*')
  .pipe(gulp.dest('htdocs/lang/'));
});
*/

gulp.task('env-db', function(cb) {
  execute(
      'gunzip -c tests/data/fa_test.sql.gz | mysql -u travis -D fa_test',
      null,
      cb
    );
});

gulp.task('env-test', ['env-db'], function() {});

gulp.task('test', ['env-test'], function(cb) {
  var command = '';
  var withCoverage = false;
  if (withCoverage) {
    command = '/usr/bin/env php vendor/bin/phpunit --coverage-html ./wiki/code_coverage tests/*_Test.php';
  } else {
    command = '/usr/bin/env php vendor/bin/phpunit -c phpunit.xml';
  }
  execute(command, null, function(err) {
    cb(null); // Swallow the error propagation so that gulp doesn't display a nodejs backtrace.
  });
});

gulp.task('test-watch', function() {
  gulp.watch([paths.testUnit, paths.src], ['test']);
});

