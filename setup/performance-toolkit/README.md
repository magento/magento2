## Performance Toolkit

## Overview

The Performance Toolkit enables you to test the performance of your Magento installations and the impact of your customizations. It allows you to generate sample data for testing performance and torun Apache JMeter scenarios, which imitate users activity. As a result, you get a set of metrics, that you can use to judge how changes affect performance, and the overall load capacity of your server(s).

## Installation

### Apache JMeter

- Go tothe [Download Apache JMeter](http://jmeter.apache.org/download_jmeter.cgi) page and download JMeter in the *Binaries* section. Note that Java 8 or later is required.
- Unzip the archive.

### JSON Plugins
- Go to the [JMeter Installing Plugins](https://jmeter-plugins.org/install/Install/) page.
- Download `plugins-manager.jar` and put it into the `{JMeter path}/lib/ext` directory. Then restart JMeter.
- Follow the instructions provided on the [JMeter Plugins Manager](https://jmeter-plugins.org/wiki/PluginsManager/) page to open Plugins Manager.
- Select *Json Plugins* from the plugins listed on the *Available Plugins* tab, then click the *Apply changes and restart JMeter* button.

## Quick Start

Before running the JMeter tests for the first time, you will need to first use the `php bin/magento setup:performance:generate-fixtures {profile path}` command to generate the test data. You can find the configuration files of available profiles in the folders `setup/performance-toolkit/profiles/ce` and `setup/performance-toolkit/profiles/ee`.

It can take a significant amount of time to generate a profile. For example, generating the medium profile takes up to 6 hours, while generating the large profile can take up to 22 hours. So we recommend using the `-s` option to skip indexation. Then you can start indexation manually.

Splitting generation and indexation processes doesn't reduce total processing time, but it requires fewer resources. For example, to generate a small profile, use commands:

    php bin/magento setup:performance:generate-fixtures -s setup/performance-toolkit/profiles/ce/small.xml
    php bin/magento indexer:reindex

For more information about the available profiles and generating fixtures generation, read [Generate data for performance testing](http://devdocs.magento.com/guides/v2.2/config-guide/cli/config-cli-subcommands-perf-data.html).

**Note:** Before generating medium or large profiles, it may be necessary to increase the value of `tmp_table_size` and `max_heap_table_size` parameters for MySQL to 512Mb or more. The value of `memory_limit` for PHP should be 1Gb or more.

There are two JMeter scenarios located in `setup/performance-toolkit` folder: `benchmark.jmx` and `benchmark_2015.jmx` (legacy version).

**Note:** To be sure that all quotes are empty, run the following MySQL query before each run of a scenario:

    UPDATE quote SET is_active = 0 WHERE is_active = 1;

### Run JMeter scenario via console

The following parameters can be passed to the `benchmark.jmx` scenario:

| Parameter Name                    | Default Value       | Description                                                                              |
| --------------------------------- | ------------------- | ---------------------------------------------------------------------------------------- |
| host                              |                     | URL component 'host' of application being tested (URL or IP).                            |
| base_path                         |                     | Base path for tested site.                                                               |
| admin_path                        | backend             | Admin backend path.                                                                      |
| admin_user                        | admin               | Admin backend user.                                                                      |
| admin_password                    | 123123q             | Admin backend password.                                                                  |
| customer_password                 | 123123q             | Storefront customer password.                                                            |
| customers_page_size               | 20                  | Page size for customers grid in Magento Admin.                                           |
| files_folder                      | ./files/            | Path to various files that are used in scenario (`setup/performance-toolkit/files`).     |
| loops                             | 1                   | Number of loops to run.                                                                  |
| orders_page_size                  | 500                 | Page size for orders grid.                                                               |
| test_duration                     | 900                 | Total duration (s) of scenario execution.                                                |
| numberOfThreads                   | 48                  | Total number of all threads.                                                             |
| frontEndPoolPercentage            | 90                  | Percentage of Frontend Pool.                                                             |
| adminPoolPercentage               | 10                  | Percentage of Admin Pool.                                                                |
| browseCatalogPercentage           | 30                  | Percentage of threads in Frontend Pool that emulate catalog browsing activities.         |
| siteSearchPercentage              | 30                  | Percentage of threads in Frontend Pool that emulate catalog search activities.           |
| checkoutByGuestPercentage         | 4                   | Percentage of threads in Frontend Pool that emulate checkout by guest.                   |
| checkoutByCustomerPercentage      | 4                   | Percentage of threads in Frontend Pool that emulate checkout by customer.                |
| addToCartPercentage               | 28                  | Percentage of threads in Frontend Pool that emulate abandoned cart activities.           |
| addToWishlistPercentage           | 2                   | Percentage of threads in Frontend Pool that emulate adding products to Wishlist.         |
| compareProductsPercentage         | 2                   | Percentage of threads in Frontend Pool that emulate products comparison.                 |
| productCompareDelay               | 0                   | Delay (s) between iterations of product comparison.                                      |
| promotionRulesPercentage          | 10                  | Percentage of threads in Admin Pool that emulate creation of promotion rules.            |
| adminPromotionsManagementDelay    | 0                   | Delay (s) between creation of promotion rules.                                           |
| merchandizingPercentage           | 50                  | Percentage of threads in Admin Pool that emulate merchandizing activities.               |
| adminProductManagementPercentage  | 90                  | Percentage of threads in Merchandizing Pool that emulate product management activities.  |
| adminCategoryManagementPercentage | 10                  | Percentage of threads in Merchandizing Pool that emulate category management activities. |
| adminProductEditingPercentage     | 60                  | Percentage of threads in Product Management Pool that emulate product editing.           |
| adminProductCreationPercentage    | 40                  | Percentage of threads in Product Management Pool that emulate creation of products.      |
| adminCategoryManagementDelay      | 0                   | Delay (s) between iterations of category management activities.                          |
| apiProcessOrdersPercentage        | 30                  | Percentage of threads in Admin Pool that emulate orders processing activities.           |
| adminProcessReturnsPercentage     | 10                  | Percentage of threads in Admin Pool that emulate creation/processing of returns.         |
| csrPoolPercentage                 | 0                   | Percentage of CSR Pool.                                                                  |
| csrBrowseCustomersPercentage      | 10                  | Percentage of threads in CSR Pool that emulate customers browsing activities.            |
| csrCreateOrderPercentage          | 70                  | Percentage of threads in CSR Pool that emulate creation of orders.                       |
| csrCreateProcessReturnsPercentage | 20                  | Percentage of threads in CSR Pool that emulate creation/processing of returns.           |
| csrCreateProcessReturnsDelay      | 0                   | Delay (s) between creation of returns.                                                   |
| wishlistDelay                     | 0                   | Delay (s) between adding products to Wishlist.                                           |
| categories_count                  | 200                 | Total number of categories that are be used in scenario.                                 |
| simple_products_count             | 30                  | Total number of simple products that are be used in scenario.                            |
| nested_categories_count           | 50                  | Total number of last-level categories that can be used in scenario.                      |

Parameters must be passed to command line with the `J` prefix:

`-J{parameter_name}={parameter_value}`

The required parameters are `{host}` and `{base_path}`. All other parameters are optional. If you do not pass any custom value, a default value will be used.

There are some options that you should pass to JMeter in the console mode:

`-n` Run scenario in Non-GUI mode
`-t` Path to the JMX file to be run
`-l` Path to the JTL file to log sample results to
`-j` Path to JMeter run log file

To get more details about available JMeter options, read [Non-GUI Mode](http://jmeter.apache.org/usermanual/get-started.html#non_gui).

For example, you can run a scenario via console with 100 threads for 5 minutes as follows:

    cd {JMeter path}/bin/
    jmeter -n -t {path to peformance toolkit}/benchmark.jmx -j ./jmeter.log -l ./jmeter-results.jtl -Jhost=magento2.dev -Jbase_path=/ -Jadmin_path=admin -Jtest_duration=300 -JnumberOfThreads=100

As a result, you will get `jmeter.log` and `jmeter-results.jtl`. The`jmeter.log` contains information about the test run and can be helpful in determining the cause of an error.  The JTL file is a text file containing the results of a test run. It can be opened in GUI mode to perform analysis of the results (see the *Output* section below).


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

**Note:** Use the GUI mode only for scenario debugging and viewing reports. Use the console mode for real-life load testing, because it requires significantly fewer resources.

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

### Threads

`benchmark.jmx` scenario has the following thread groups and default percentage breakdown:

- **Frontend Pool** (90%)

| Thread Group Name         | Label Suffix                                           | % of Pool |
| ------------------------- | ------------------------------------------------------ | --------- |
| Catalog Browsing By Guest | Catalog Browsing By Guest                              | 30        |
| Site Search               | SearchQuick, SearchQuickWithFilter and SearchAdvanced  | 30        |
| Add To Cart (Guest)       | Add To Cart By Guest                                   | 28        |
| Add to Wishlist           | WishList                                               | 2         |
| Compare Products          | Product Compare By Guest                               | 2         |
| Checkout By Guest         | Checkout By Guest                                      | 4         |
| Checkout By Customer      | Checkout By Customer                                   | 4         |

Site Search thread group contains 3 variations:
- Quick Search (65%)
- Quick Search With Filtering (35%)
- Advanced Search (5%)

- **Admin Pool** (10%)

| Thread Group Name                         | Label Suffix | % of Pool |
| ----------------------------------------- | ------------ | --------- |
| *Merchandizing Pool* (see below)          | -            | 50        |
| Admin Promotion Rules                     | -            | 10        |
| Admin API - Process Orders                | -            | 30        |
| Admin Create/Process Returns              | -            | 10        |

*Merchandizing Pool* (50% of Admin Pool)

| Thread Group Name                         | Label Suffix | % of Pool |
| ----------------------------------------- | ------------ | --------- |
| *Product Management Pool* (see below)     | -            | 90        |
| Admin Category Management (Merchandizing) | -            | 10        |

*Product Management Pool* (90% of Merchandizing Pool)

| Thread Group Name                         | Label Suffix | % of Pool |
| ----------------------------------------- | ------------ | --------- |
| Admin Edit Product (Merchandizing)        | -            | 60        |
| Admin Create Product (Merchandizing)      | -            | 40        |

- **CSR Pool** (0%)

| Thread Group Name          | Label Suffix | % of Pool |
| -------------------------- | ------------ | --------- |
| CSR Browse Customers       | -            | 10        |
| CSR Create Order           | -            | 70        |
| CSR Create/Process Returns | -            | 20        |

The number of threads in each group can be calculated by formula:

    N = {numberOfThreads} * {poolPercentage} * {threadGroupPercentage} / 10000

For example, the value of {numberOfThreads} parameter is 100 and we want to get the number of threads in the *Checkout By Guest* group:

    N = 100 * 90 * 4 / 10000 = 4

The *Merchandizing Pool* and the *Product Management Pool* should be taken into account when the number of product or category management threads needs to be calculated. For example, the number of threads in *Admin Create Product (Merchandizing)* group is calculated as follows:

    N = {numberOfThreads} * {adminPoolPercentage} * {merchandizingPercentage} * {adminProductManagementPercentage} * {adminProductCreationPercentage} / 100 / 100 / 100 / 100

If {numberOfThreads} equals to 100:

    N = 100 * 10 * 50 * 90 * 40 / 100000000 = 2

To change the percentage breakdown, pass custom values for each pool or thread group to the script. For example, to run the scenario with *Frontend Pool* only enabled, run the script with the following parameters

    -JfrontEndPoolPercentage=100 -JadminPoolPercentage=0 -JcsrPoolPercentage=0.

**Legacy Threads**

The `benchmark_2015.jmx` script consists of five thread groups: the setup thread and four user threads.
By default, the percentage ratio between thread groups is as follows:
- Browsing, adding items to the cart and abandon cart (BrowsAddToCart suffix in reports) - 62%
- Just browsing (CatProdBrows suffix in reports) - 30%
- Browsing, adding items to cart and checkout as guest (GuestChkt suffix in reports) -  4%
- Browsing, adding items to cart and checkout as registered customer (CustomerChkt suffix in reports) - 4%

### Results Interpretation

In order to build an aggregate report from the results of the `benchmark.kmx` scenario run, use the script `generate-b2c.php` in the folder `setup/performance-toolkit/aggregate-report`.

The script parses the JTL file and generates an aggregate report in CSV format. The report consists of the 4 sections separated by two empty lines:

1. Summary information: Checkouts Per Hour, Page Views Per Hour and Test Duration (in seconds)
2. Aggregated information about all requests within each thread group (median time, average time, min/max, amount of hits per hour, etc.)
3. Aggregated information about common requests (open home page, category page, product page, login, etc.) accross the entire scenario
4. List of the requests that weren't executed during the scenario run

Also, the aggregate report can include information about the memory usage for each request type. This requires additional configuration. You should add the following code at the end of `pub/index.php`:

    if (strpos($_SERVER['REQUEST_URI'], '/banner/ajax/load/') === false) {
        if (!file_exists('../var/log/memory_usage.log')) {
            file_put_contents('../var/log/memory_usage.log', str_pad('Usage', 12, ' ', STR_PAD_LEFT) . ' ' . str_pad('Real Usage', 12, ' ', STR_PAD_LEFT) . '  URI' . "\n", FILE_APPEND | LOCK_EX);
        }
        $result = str_pad(memory_get_peak_usage(), 12, ' ', STR_PAD_LEFT) . ' ' . str_pad(memory_get_peak_usage(true), 12, ' ', STR_PAD_LEFT) . '  ' . $_SERVER['REQUEST_URI'];
        file_put_contents('../var/log/memory_usage.log', $result . "\n", FILE_APPEND | LOCK_EX);
    }
After that, the information about memory usage for each request will be logged in the file `var/log/memory_usage.log`.

To generate the aggregate report, run the following command from the Magento root directory:

    php setup/performance-toolkit/aggregate-report/generate-b2c.php -j {path to folder with JTL file}/jmeter_report.jtl -m var/log/memory_usage.log -o aggregate_report.csv

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

For the total number of page views, sum up all actions, minus the setup thread.
