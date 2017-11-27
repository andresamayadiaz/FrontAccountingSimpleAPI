var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');
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
  testUnit: ['tests/*.php']
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

gulp.task('env-files', function() {
  gulp.src('tests/data/*.php')
    .pipe(gulp.dest('_frontaccounting/'));
  gulp.src('tests/data/company/0/*.php')
  .pipe(gulp.dest('_frontaccounting/company/0/'));
  gulp.src('tests/data/lang/*')
  .pipe(gulp.dest('_frontaccounting/lang/'));
});

gulp.task('env-db', function(cb) {
  execute(
      'gunzip -c tests/data/fa_test.sql.gz | mysql -u travis --password=\'\' -D fa_test',
      null,
      cb
    );
});

gulp.task('env-test', ['env-db'], function() {});

gulp.task('env-test-travis', ['env-db', 'env-files'], function() {});

gulp.task('test-only', function(cb) {
  var command = '';
  var withCoverage = false;
  if (withCoverage) {
    command = '/usr/bin/env php _frontaccounting/modules/api/vendor/bin/phpunit --coverage-html ./wiki/code_coverage -c _frontaccounting/modules/api/phpunit.xml';
  } else {
    command = '/usr/bin/env php _frontaccounting/modules/api/vendor/bin/phpunit -c _frontaccounting/modules/api/phpunit.xml';
  }
  execute(command, null, function(err) {
    cb(err); // Don't swallow the error propagation so that gulp does display a nodejs backtrace.
  });
});

/* To run the tests you first need to start the php server on port 8000.
 *   `sh build-startServer.sh`
 * then you can run the tests
 *   `gulp test`
 */
gulp.task('test', gulpSequence('env-test', 'test-only'));

gulp.task('test-travis', gulpSequence('env-test-travis', 'test-only'));

gulp.task('test-watch', function() {
  gulp.watch([paths.testUnit, paths.src], ['test']);
});

gulp.task('package-zip', ['package-vendor'], function(cb) {
  var options = {
    dryRun: false,
    silent: false,
    src: "./",
    name: "frontaccounting",
    version: "2.4",
    release: "-api.module.1.4"
  };
  execute(
    'rm -f *.zip && cd <%= src %> && zip -r -x@./upload-exclude-zip.txt -y -q ./<%= name %>-<%= version %><%= release %>.zip .',
    options,
    cb
  );
});

gulp.task('package-tar', ['package-vendor'], function(cb) {
  var options = {
    dryRun: false,
    silent: false,
    src: "./",
    name: "frontaccounting",
    version: "2.4",
    release: "-api.module.1.4"
  };
  execute(
    'rm -f *.tgz && cd <%= src %> && tar -cvzf ./<%= name %>-<%= version %><%= release %>.tgz -X upload-exclude.txt * .htaccess',
    options,
    cb
  );
});

gulp.task('package-vendor', function(cb) {
  var options = {
    dryRun: false,
    silent: false
  };
  execute(
    'rm -rf vendor && composer install --no-dev',
    options,
    cb
  );
});

gulp.task('package', ['package-zip', 'package-tar']);

