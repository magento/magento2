var gulp    = require("gulp");
var notify  = require("gulp-notify");
var run     = require("gulp-run");
var phpunit = require("gulp-phpunit");

gulp.task("tests", function ()
{
    gulp.src("./tests/TestBase.php")
		    .pipe( run("clear") )
		        .pipe( phpunit("phpunit", {
		            debug: false,
		            notify: true
		        }))
		    .on("error", function() {
		        run("notify-send 'Tests Failed' 'Got some problems buddy.'").exec();
		    })
        	.pipe( run("notify-send 'Tests Passed' 'Nailed it.'"));
});

gulp.task("watch", function()
{
    gulp.watch([ "./src/**/*.php", "./tests/**/*"], [ "tests" ]);
})

gulp.task("default", [ "tests", "watch" ]);
