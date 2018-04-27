## Performance Toolkit

## Overview

The Performance Toolkit enables you to test the performance of your Magento installations and the impact of your customizations. It allows you to generate sample data for testing performance and to run Apache JMeter scenarios, which imitate users activity. As a result, you get a set of metrics, that you can use to judge how changes affect performance, and the overall load capacity of your server(s).

## Installation

### Apache JMeter

- Go to the [Download Apache JMeter](http://jmeter.apache.org/download_jmeter.cgi) page and download JMeter in the *Binaries* section. Note that Java 8 or later is required.
- Unzip the archive.

### JSON Plugins
- Go to the [JMeter Installing Plugins](https://jmeter-plugins.org/install/Install/) page.
- Download `plugins-manager.jar` and put it into the `{JMeter path}/lib/ext` directory. Then restart JMeter.
- Follow the instructions provided on the [JMeter Plugins Manager](https://jmeter-plugins.org/wiki/PluginsManager/) page to open Plugins Manager.
- Select *Json Plugins* from the plugins listed on the *Available Plugins* tab, then click the *Apply changes and restart JMeter* button.

## Quick Start

Before running the JMeter tests for the first time, you will need to first use the `php bin/magento setup:performance:generate-fixtures {profile path}` command to generate the test data.
You can find the configuration files of available B2C profiles in the folders `setup/performance-toolkit/profiles/ce` and `setup/performance-toolkit/profiles/ee`.

It can take a significant amount of time to generate a profile. For example, generating the large profile can take up to 4 hours. So we recommend using the `-s` option to skip indexation. Then you can start indexation manually.

Splitting generation and indexation processes doesn't reduce total processing time, but it requires fewer resources. For example, to generate a small profile, use commands:

    php bin/magento setup:performance:generate-fixtures -s setup/performance-toolkit/profiles/ce/small.xml
    php bin/magento indexer:reindex

For more information about the available profiles and generating fixtures generation, read [Generate data for performance testing](http://devdocs.magento.com/guides/v2.2/config-guide/cli/config-cli-subcommands-perf-data.html).

For run Admin Pool in multithreading mode, please be sure, that:
  - "Admin Account Sharing" is enabled

    `Follow Stores > Configuration > Advanced > Admin > Security.
    Set Admin Account Sharing to Yes.`

  - Indexers setup in "Update by schedule" mode: 

    `Follow System > Tool > Index Management
    Set "Update by schedule" for all idexers`

**Note:** Before generating medium or large profiles, it may be necessary to increase the value of `tmp_table_size` and `max_heap_table_size` parameters for MySQL to 512Mb or more. The value of `memory_limit` for PHP should be 1Gb or more.

There are two JMeter scenarios located in `setup/performance-toolkit` folder: `benchmark.jmx` and `benchmark_2015.jmx` (legacy version).

**Note:** To be sure that all quotes are empty, run the following MySQL query before each run of a scenario:

    UPDATE quote SET is_active = 0 WHERE is_active = 1;

### Run JMeter scenario via console

The following parameters can be passed to the `benchmark.jmx` scenario:

| Parameter Name                                | Default Value       | Description                                                                              |
| --------------------------------------------- | ------------------- | ---------------------------------------------------------------------------------------- |
| host                                          |  localhost          | URL component 'host' of application being tested (URL or IP).                            |
| base_path                                     |       /             | Base path for tested site.                                                               |
| admin_path                                    | admin               | Admin backend path.                                                                      |
| admin_user                                    | admin               | Admin backend user.                                                                      |
| admin_password                                | 123123q             | Admin backend password.                                                                  |
| customer_password                             | 123123q             | Storefront customer password.                                                            |
| customers_page_size                           | 50                  | Page size for customers grid in Magento Admin.                                           |
| files_folder                                  | ./files/            | Path to various files that are used in scenario (`setup/performance-toolkit/files`).     |
| loops                                         | 1                   | Number of loops to run.                                                                  |
| frontendPoolUsers                             | 1                   | Total number of Frontend threads.                                                        |
| adminPoolUsers                                | 1                   | Total number of Admin threads.                                                           |
| browseCatalogByGuestPercentage                | 30                  | Percentage of threads in Frontend Pool that emulate catalog browsing activities.         |
| browseCatalogByCustomerPercentage             | 0                   | Percentage of threads in Frontend Pool that emulate catalog browsing activities.         |
| siteSearchPercentage                          | 30                  | Percentage of threads in Frontend Pool that emulate catalog search activities.           |
| searchQuickPercentage                         | 60                  | Percentage of threads in Frontend Pool that emulate catalog search activities.           |
| searchQuickFilterPercentage                   | 30                  | Percentage of threads in Frontend Pool that emulate catalog search activities.           |
| searchAdvancedPercentage                      | 10                  | Percentage of threads in Frontend Pool that emulate catalog search activities.           |
| checkoutByGuestPercentage                     | 4                   | Percentage of threads in Frontend Pool that emulate checkout by guest.                   |
| checkoutByCustomerPercentage                  | 4                   | Percentage of threads in Frontend Pool that emulate checkout by customer.                |
| addToCartByGuestPercentage                    | 28                  | Percentage of threads in Frontend Pool that emulate abandoned cart activities.           |
| addToWishlistPercentage                       | 2                   | Percentage of threads in Frontend Pool that emulate adding products to Wishlist.         |
| compareProductsPercentage                     | 2                   | Percentage of threads in Frontend Pool that emulate products comparison.                 |
| productCompareDelay                           | 0                   | Delay (s) between iterations of product comparison.                                      |
| promotionRulesPercentage                      | 10                  | Percentage of threads in Admin Pool that emulate creation of promotion rules.            |
| adminPromotionsManagementDelay                | 0                   | Delay (s) between creation of promotion rules.                                           |
| adminCategoryManagementPercentage             | 10                   | Percentage of threads in Merchandising Pool that emulate category management activities. |
| adminProductEditingPercentage                 | 35                  | Percentage of threads in Merchandising Pool that emulate product editing.                |
| adminProductCreationPercentage                | 25                  | Percentage of threads in Merchandising Pool that emulate creation of products.           |
| adminPromotionRulesPercentage                 | 15                  | Percentage of threads in Admin Pool that emulate admin rules creating activities.        |
| adminCategoryManagementDelay                  | 0                   | Delay (s) between iterations of category management activities.                          |
| apiProcessOrders                              | 5                   | Number of orders for process in Admin API - Process Orders.                              |
| adminEditOrderPercentage                      | 15                  | Percentage of threads in Admin Pool that emulate order edit.                             |
| csrPoolUsers                                  | 0                   | Users of Customer Support Request (CSR) Pool.                                            |
| othersPoolUsers                               | 0                   | Users of Others Pool.                                                                    |
| browseCustomerGridPercentage                  | 10                  | Percentage of threads in CSR Pool that emulate customers browsing activities.            |
| adminCreateOrderPercentage                    | 70                  | Percentage of threads in CSR Pool that emulate creation of orders.                       |
| adminReturnsManagementPercentage              | 20                  | Percentage of threads in CSR Pool that emulate creation/processing of returns.           |
| adminCreateProcessReturnsDelay                | 0                   | Delay (s) between creation of returns.                                                   |
| wishlistDelay                                 | 0                   | Delay (s) between adding products to Wishlist.                                           |
| categories_count                              | 100                 | Total number of categories that are be used in scenario.                                 |
| simple_products_count                         | 30                  | Total number of simple products that are be used in scenario.                            |

Parameters must be passed to the command line with the `J` prefix:

`-J{parameter_name}={parameter_value}`

The required parameters are `{host}` and `{base_path}`. All other parameters are optional. If you do not pass any custom value, a default value will be used.

There are some options that you should pass to JMeter in the console mode:

`-n` Run scenario in Non-GUI mode
`-t` Path to the JMX file to be run
`-l` Path to the JTL file to log sample results to
`-j` Path to JMeter run log file

To get more details about available JMeter options, read [Non-GUI Mode](http://jmeter.apache.org/usermanual/get-started.html#non_gui).

For example, you can run the B2C scenario via console with 90 threads for the Frontend Pool and 10 threads for the Admin Pool:

    cd {JMeter path}/bin/
    jmeter -n -t {path to peformance toolkit}/benchmark.jmx -j ./jmeter.log -l ./jmeter-results.jtl -Jhost=magento2.dev -Jbase_path=/ -Jadmin_path=admin -JfrontendPoolUsers=90 -JadminPoolUsers=10

As a result, you will get `jmeter.log` and `jmeter-results.jtl`. The`jmeter.log` contains information about the test run and can be helpful in determining the cause of an error.  The JTL file is a text file containing the results of a test run. It can be opened in the GUI mode to perform analysis of the results (see the *Output* section below).


The following parameters can be passed to the `benchmark_2015.jmx` scenario:

| Parameter Name                   | Default Value       | Description                                                                                                                               |
| -------------------------------- | ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| host                             | localhost           | URL component 'host' of application being tested (URL or IP).                                                                             |
| base_path                        | /                   | Base path for tested site.                                                                                                                |
| report_save_path                 | ./                  | Path where reports will be saved. Reports will be saved in current working directory by default.                                          |
| ramp_period                      | 300                 | Ramp period (seconds). Period the request will be distributed within.                                                                     |
| orders                           | 0                   | Number of orders in the period specified in the current allocation. If `orders` is specified, the `users` parameter will be recalculated. |
| users                            | 100                 | Number of concurrent users. Recommended amount is 100. Minimal amount is 10.                                                              |
| view_product_add_to_cart_percent | 62                  | Percentage of users that will only reach the add to cart stage.                                                                           |
| view_catalog_percent             | 30                  | Percentage of users that will only reach the view catalog stage.                                                                          |
| guest_checkout_percent           | 4                   | Percentage of users that will reach the guest checkout stage.                                                                             |
| customer_checkout_percent        | 4                   | Percentage of users that will reach the (logged-in) customer checkout stage.                                                              |
| loops                            | 1                   | Number of loops to run.                                                                                                                   |
| admin_path                       | admin               | Admin backend path.                                                                                                                       |
| admin_user                       | admin               | Admin backend user.                                                                                                                       |
| admin_password                   | 123123q             | Admin backend password.                                                                                                                   |
| think_time_deviation             | 1000                | Deviation (ms) for "think time" emulation.                                                                                                |
| think_time_delay_offset          | 2000                | Constant delay offset (ms) for "think time" emulation.                                                                                    |

### Run JMeter scenario via GUI

**Note:** Use the GUI mode only for scenario debugging and viewing reports. Use console mode for real-life load testing, because it requires significantly fewer resources.

- Change directories to `{JMeter path}/bin/` and run `jmeter.bat`.
- Click *File -> Open (Ctrl+O)* and select `benchmark.jmx` file or drag and drop the `benchmark.jmx` file in the opened GUI.

In the root node (*Performance Test Plan*) in the left panel, you can change *User Defined Variables* listed in the previous section.
To run a script, click the *Start* button (green arrow in the top menu).

## Output

The results of running a JMeter scenario are available in the *View Results Tree* and *Aggregate Report* nodes in the left panel of the JMeter GUI.

When the script is run via GUI, the results are available in the left panel. Choose the corresponding report. When the script is run via console, a JTL report is generated. You can run JMeter GUI later and open it in the corresponding report node.

The legacy scenario (Benchmark_2015) contains *View Results Tree*, *Detailed URLs Report* and *Summary Report* nodes.

### View Results Tree

This report shows the tree of all requests and responses made during the scenario run.  It provides information about the response time, headers and response codes. This report is useful for scenario debugging, but should be disabled during load testing because it consumes a lot of resources.

You can open a JTL file in this report to debug a scenario and view the requests that cause errors. By default, a JTL file doesn't contain bodies of requests/responses, so it is better to debug scenarios in the GUI mode.

For more details, read [View Results Tree](http://jmeter.apache.org/usermanual/component_reference.html#View_Results_Tree).

### Aggregate Report

This report contains aggregated information about all requests. It provides request count, min, max, average, error rate, approximate throughput, etc. You can open a JTL file in this report to analyze the results of a scenario run.

For more details, read [Aggregate Report](http://jmeter.apache.org/usermanual/component_reference.html#Aggregate_Report).

### Detailed URLs Report (Legacy)

This report contains information about URLs. Note that the URL is displayed only in a generated report file (URL is not displayed in the GUI). The report file name is `{report_save_path}/detailed-urls-report.log`.  It can be opened as a CSV file.

For more details, read [View Results in Table](http://jmeter.apache.org/usermanual/component_reference.html#View_Results_in_Table).

### Summary Report (Legacy)

The report contains aggregated information about threads. The report file name is `{report_save_path}/summary-report.log`.

For more details, read [Summary Report](http://jmeter.apache.org/usermanual/component_reference.html#Summary_Report).

## Additional Information

### Scenarios

`benchmark.jmx` scenario has the following pools and default percentage breakdown for each scenario:

**Frontend Pool** (frontendPoolUsers)

| Scenario Name             | % of Pool |
| ------------------------- | --------- |
| Catalog Browsing By Guest |     30    |
| Site Search               |     30    |
| Add To Cart By Guest      |     28    |
| Add to Wishlist           |     2     |
| Compare Products          |     2     |
| Checkout By Guest         |     4     |
| Checkout By Customer      |     4     |

Site Search thread group contains 3 variations:
- Quick Search (60%)
- Quick Search With Filtration (30%)
- Advanced Search (10%)

**Admin Pool** (adminPoolUsers)

| Scenario Name               |% of Pool  |
| ----------------------------| --------- |
| Admin Promotion Rules       | 15        |
| Admin Edit Order            | 15        |
| Admin Category Management   | 10        |
| Admin Edit Product          | 35        |
| Admin Create Product        | 25        |

**CSR Pool** (csrPoolUsers)

| Scenario Name              | % of Pool |
| -------------------------- | --------- |
| Browse Customer Grid       | 10        |
| Admin Create Order         | 70        |
| Admin Returns Management   | 20        |

**Legacy Threads**

The `benchmark_2015.jmx` script consists of five thread groups: the setup thread and four user threads.
By default, the percentage ratio between the thread groups is as follows:
- Browsing, adding items to the cart and abandon cart (BrowsAddToCart suffix in reports) - 62%
- Just browsing (CatProdBrows suffix in reports) - 30%
- Browsing, adding items to cart and checkout as guest (GuestChkt suffix in reports) -  4%
- Browsing, adding items to cart and checkout as registered customer (CustomerChkt suffix in reports) - 4%

**Legacy Scenario**

It is convenient to use *Summary Report* for the results analysis. To evaluate the number of each request per hour, use the value in the *Throughput* column.

To get the summary value of throughput for some action:
1. Find all rows that relate to the desired action
2. Convert values from *Throughput* column to a common denominator
3. Sum up the obtained values

For example, to get summary throughput for the *Simple Product View* action when the following rows are present in the *Summary Report*:

| Label                                 | # Samples       | ... | Throughput |
| ------------------------------------- | --------------- | --- | ---------- |
| ...                                   | ...             | ... | ...        |
| Open Home Page(CatProdBrows)          | 64              | ... | 2.2/sec    |
| Simple Product 1 View(GuestChkt)      | 4               | ... | 1.1/sec    |
| Simple Product 2 View(BrowsAddToCart) | 30              | ... | 55.6/min   |
| ...                                   | ...             | ... | ...        |

Find all rows with the label *Simple Product # View* and calculate the summary throughput:

    1.1/sec + 55.6/min = 66/min + 55.6/min = 121.6/min = 2.02/sec

If you need information about the summary throughput of the *Checkout* actions, find the rows with labels *Checkout success* and make the same calculation.

For the total number of page views, you will want to sum up all actions minus the setup thread.
