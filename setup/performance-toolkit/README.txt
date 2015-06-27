Performance Toolkit
=============

Installation
-----------
jMeter:
-- go to http://jmeter.apache.org/download_jmeter.cgi and download jMeter in Binary section (pay you attention that Java 6 or later is required)
-- unzip archive

Plugins (only if you want to use additional reports (like graphs)):
-- go to http://jmeter-plugins.org/downloads/all/ and download JMeterPlugins-Standard and JMeterPlugins-Extras
-- unzip them to appropriate ext directory of your jMeter instance.
-- additional reports can now be added via GUI.


Usage
-----------
Before running the jMeter tests for the first time, you will need to first use bin/magento setup:performance:generate-fixtures command to generate the test data.

1. Run via console
Scenario can accept parameters that are described bellow in format <parameter_name:default_value>:

<host:localhost> URL component 'host' of application being tested (URL or IP). Default is 'localhost'.
<base_path:/> Base path for tested site. Default is '/'.
<report_save_path:./> Path where reports will be saved. Reports will be saved in current working directory by default. Default is './'.
<ramp_period:300> Ramp period (seconds). Period the request will be distributed within. Default is '300'.
<orders:0> Number of orders in the period specified in the current allocation. If <orders> is specified, the <users> parameter will be recalculated. Default is '0'.
<users:100> Number of concurrent users. Recommended  amount is 100. Minimal amount is 10. Default is '100'.
<view_product_add_to_cart_percent:62/> Percentage of users that will only reach the add to cart stage. Default is '62'.
<view_catalog_percent:30/> Percentage of users that will only reach the view catalog stage. Default is '30'.
<guest_checkout_percent:4/> Percentage of users that will reach the guest checkout stage. Default is '4'.
<customer_checkout_percent:4/> Percentage of users that will reach the (logged-in) customer checkout stage. Default is '4'.
<loops:1/> Number of loops to run. Default is '1'.
<admin_path:admin/> Admin backend path. Default is 'admin'.
<admin-user:admin/> Admin backend user. Default is 'admin'.
<admin-password:123123q/> Admin backend password. Default is '123123q'.
<think_time_deviation:1000> Deviation (ms) for "think time" emulation. Default is '1000'.
<think_time_delay_offset:2000> Constant delay offset (ms) for "think time" emulation. Default is '2000'.

Necessary parameters must be passed to command line with "J" prefix: "-J<parameter_name>=<parameter_value>"

Example:
> cd /directory_of_jMeter/bin/
> jmeter -n -t /path_to_benchmark_file/benchmark.jmx -Jhost=magento2.dev -Jbase_path=/ -Jusers=100 -Jramp_period=300 -Jreport_save_path=./

2. Run via GUI
-- Open jMeter/bin directory and run jmeter.bat
-- Click in menu File -> Open (Ctrl+O) and select file; or drag and drop benchmark.jmx file in opened GUI.

On the first tab 'Test Toolkit' you can change 'User Defined variables' like as <host>, <users>, <ramp_period>, <orders>, <report_save_path>.
For running script click "Start" (green arrow in the top menu).


Results of running (Report types)
-----------

After running via GUI you can see result of working in left panel. Choose the corresponding report.
After running script via console report will be generated in the path that  has been specified in <report_save_path>.


Threads
-----------

jMeter script consists of five threads. Setup thread and four user threads.
Percentage ratio between threads is as follows:
Browsing, adding items to the cart and abandon cart (BrowsAddToCart suffix in reports) - 62%
Just browsing (BrowsAddToCart suffix in reports) - 30%
Browsing, adding items to cart and checkout it as guest (GuestChkt suffix in reports) -  4%
Browsing, adding items to cart and checkout as registered customer (CustomerChkt suffix in reports) - 4%


About reports:
-----------

Summary Report.
Report contains aggregated information about threads.
Report file name is {report_save_path}/summary-report.log
Details http://jmeter.apache.org/usermanual/component_reference.html#Summary_Report

Detailed URLs report.
Report contains information about URLs.
Pay your attention that URL is displayed only in generated report file (in GUI, URL is not displayed).
Report file name is {report_save_path}/detailed-urls-report.log (can be open as csv format).
Details http://jmeter.apache.org/usermanual/component_reference.html#View_Results_in_Table

About other types read on
http://jmeter.apache.org/usermanual/component_reference.html
