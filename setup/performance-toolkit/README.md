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

For more information about the available profiles and generating fixtures generation, read [Generate data for performance testing](https://devdocs.magento.com/guides/v2.3/config-guide/cli/config-cli-subcommands-perf-data.html).

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

Main parameters:

| Parameter Name                                | Default Value       | Description                                                                              |
| --------------------------------------------- | ------------------- | ---------------------------------------------------------------------------------------- |
| host                                          |  localhost          | URL component 'host' of application being tested (URL or IP).                            |
| base_path                                     |       /             | Base path for tested site.                                                               |
| files_folder                                  | ./files/            | Path to various files that are used in scenario (`setup/performance-toolkit/files`).     |
| request_protocol                              | http                | Hypertext Transfer Protocol (http or https).                                             |
| graphql_port_number                           |                     | Port number for GraphQL.                                                                 |
| admin_password                                | 123123q             | Admin backend password.                                                                  |
| admin_path                                    | admin               | Admin backend path.                                                                      |
| admin_user                                    | admin               | Admin backend user.                                                                      |
| cache_hits_percentage                         | 100                 | Cache hits percentage.                                                                   |
| seedForRandom                                 | 1                   | System option for setting random number method                                           |
| loops                                         | 1                   | Number of loops to run.                                                                  |
| frontendPoolUsers                             | 0                   | Total number of Frontend threads.                                                        |
| adminPoolUsers                                | 0                   | Total number of Admin threads.                                                           |
| csrPoolUsers                                  | 0                   | Total number of CSR threads.                                                             |
| apiPoolUsers                                  | 0                   | Total number of API threads.                                                             |
| oneThreadScenariosPoolUsers                   | 0                   | Total number of One Thread Scenarios threads.                                            |
| graphQLPoolUsers                              | 0                   | Total number of GraphQL threads.                                                         |
| combinedBenchmarkPoolUsers                    | 0                   | Total number of Combined Benchmark threads.                                              |

Parameters for Frontend pool:

| Parameter Name                                | Default Value       | Description                                                                               |
| --------------------------------------------- | ------------------- | ----------------------------------------------------------------------------------------- |
| browseCatalogByCustomerPercentage             |  0                  | Percentage of threads in Frontend Pool that emulate customer catalog browsing activities. |
| browseCatalogByGuestPercentage                |  0                  | Percentage of threads in Frontend Pool that emulate guest catalog browsing activities.    |
| siteSearchPercentage                          |  0                  | Percentage of threads in Frontend Pool that emulate catalog search activities.            |
| addToCartByGuestPercentage                    |  0                  | Percentage of threads in Frontend Pool that emulate abandoned cart activities by guest.   |
| addToWishlistPercentage                       |  0                  | Percentage of threads in Frontend Pool that emulate adding products to Wishlist.          |
| compareProductsPercentage                     |  0                  | Percentage of threads in Frontend Pool that emulate products comparison.                  |
| checkoutByGuestPercentage                     |  0                  | Percentage of threads in Frontend Pool that emulate checkout by guest.                    |
| checkoutByCustomerPercentage                  |  0                  | Percentage of threads in Frontend Pool that emulate checkout by customer.                 |
| reviewByCustomerPercentage                    |  0                  | Percentage of threads in Frontend Pool that emulate reviewing products.                   |
| addToCartByCustomerPercentage                 |  0                  | Percentage of threads in Frontend Pool that emulate abandoned cart activities by customer.|
| accountManagementPercentage                   |  0                  | Percentage of threads in Frontend Pool that emulate account management.                   |

Parameters for Admin pool:

| Parameter Name                                | Default Value       | Description                                                                               |
| --------------------------------------------- | ------------------- | ----------------------------------------------------------------------------------------- |
| adminCMSManagementPercentage                  |  0                  | Percentage of threads in Admin Pool that emulate CMS management activities.               |
| browseProductGridPercentage                   |  0                  | Percentage of threads in Admin Pool that emulate products grid browsing activities.       |
| browseOrderGridPercentage                     |  0                  | Percentage of threads in Admin Pool that emulate orders grid browsing activities.         |
| adminProductCreationPercentage                |  0                  | Percentage of threads in Admin Pool that emulate product creation activities.             |
| adminProductEditingPercentage                 |  0                  | Percentage of threads in Admin Pool that emulate product editing activities.              |

Parameters for CSR pool:

| Parameter Name                                | Default Value       | Description                                                                               |
| --------------------------------------------- | ------------------- | ----------------------------------------------------------------------------------------- |
| adminReturnsManagementPercentage              |  0                  | Percentage of threads in CSR Pool that emulate admin returns management activities.       |
| browseCustomerGridPercentage                  |  0                  | Percentage of threads in CSR Pool that emulate customers grid browsing activities.        |
| adminCreateOrderPercentage                    |  0                  | Percentage of threads in CSR Pool that emulate creating orders activities.                |

Parameters for API pool:

| Parameter Name                                | Default Value       | Description                                                                               |
| --------------------------------------------- | ------------------- | ----------------------------------------------------------------------------------------- |
| apiBasePercentage                             |  0                  | Percentage of threads in API Pool that emulate API requests activities.                   |

Parameters for One Thread Scenarios pool:

| Parameter Name                                | Default Value       | Description                                                                   |
| --------------------------------------------- | ------------------- | ----------------------------------------------------------------------------- |
| productGridMassActionPercentage               |  0                  | Percentage of threads that emulate product mass action activities.            |
| importProductsPercentage                      |  0                  | Percentage of threads that emulate products import activities.                |
| importCustomersPercentage                     |  0                  | Percentage of threads that emulate customers import activities.               |
| exportProductsPercentage                      |  0                  | Percentage of threads that emulate products export activities.                |
| exportCustomersPercentage                     |  0                  | Percentage of threads that emulate customers export activities.               |
| apiSinglePercentage                           |  0                  | Percentage of threads that emulate API nonparallel requests activities.       |
| adminCategoryManagementPercentage             |  0                  | Percentage of threads that emulate category management activities.            |
| adminPromotionRulesPercentage                 |  0                  | Percentage of threads that emulate promotion rules activities.                |
| adminCustomerManagementPercentage             |  0                  | Percentage of threads that emulate customer management activities.            |
| adminEditOrderPercentage                      |  0                  | Percentage of threads that emulate edit order activities.                     |
| catalogGraphQLPercentage                      |  0                  | Percentage of threads that emulate nonparallel catalogGraphQL activities.     |

Parameters for GraphQL pool:

| Parameter Name                                                    | Default Value       | Description                                                                               |
| ----------------------------------------------------------------- | ------------------- | ----------------------------------------------------------------------------------------- |
| graphqlGetListOfProductsByCategoryIdPercentage                    |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetSimpleProductDetailsByProductUrlKeyPercentage           |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetSimpleProductDetailsByNamePercentage                    |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetConfigurableProductDetailsByProductUrlKeyPercentage     |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetConfigurableProductDetailsByNamePercentage              |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetProductSearchByTextAndCategoryIdPercentage              |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetCategoryListByCategoryIdPercentage                      |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlUrlInfoByUrlKeyPercentage                                  |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetCmsPageByIdPercentage                                   |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetNavigationMenuByCategoryIdPercentage                    |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlCreateEmptyCartPercentage                                  |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlGetEmptyCartPercentage                                     |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlSetShippingAddressOnCartPercentage                         |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlSetBillingAddressOnCartPercentage                          |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlAddSimpleProductToCartPercentage                           |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlAddConfigurableProductToCartPercentage                     |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlUpdateSimpleProductQtyInCartPercentage                     |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlUpdateConfigurableProductQtyInCartPercentage               |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlRemoveSimpleProductFromCartPercentage                      |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlRemoveConfigurableProductFromCartPercentage                |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlApplyCouponToCartPercentage                                |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlRemoveCouponFromCartPercentage                             |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlCatalogBrowsingByGuestPercentage                           |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |
| graphqlCheckoutByGuestPercentage                                  |  0                  | Percentage of threads in GraphQL Pool that emulate GraphQL requests activities.           |

Parameters for Combined Benchmark pool:

| Parameter Name                                                    | Default Value       | Description                                                                                          |
| ----------------------------------------------------------------- | ------------------- | ---------------------------------------------------------------------------------------------------- |
| cBrowseCatalogByGuestPercentage                                   |  29                 | Percentage of threads in Combined Benchmark Pool that emulate customer catalog browsing activities.  |
| cSiteSearchPercentage                                             |  29                 | Percentage of threads in Combined Benchmark Pool that emulate catalog search activities.             |
| cAddToCartByGuestPercentage                                       |  26                 | Percentage of threads in Combined Benchmark Pool that emulate abandoned cart activities.             |
| cAddToWishlistPercentage                                          |  1.5                | Percentage of threads in Combined Benchmark Pool that emulate adding products to Wishlist.           |
| cCompareProductsPercentage                                        |  1.5                | Percentage of threads in Combined Benchmark Pool that emulate products comparison.                   |
| cCheckoutByGuestPercentage                                        |  3.5                | Percentage of threads in Combined Benchmark Pool that emulate checkout by guest.                     |
| cCheckoutByCustomerPercentage                                     |  3.5                | Percentage of threads in Combined Benchmark Pool that emulate checkout by customer.                  |
| cAccountManagementPercentage                                      |  1                  | Percentage of threads in Combined Benchmark Pool that emulate account management.                    |
| cAdminCMSManagementPercentage                                     |  0.35               | Percentage of threads in Combined Benchmark Pool that emulate CMS management activities.             |
| cAdminBrowseProductGridPercentage                                 |  0.2                | Percentage of threads in Combined Benchmark Pool that emulate products grid browsing activities.     |
| cAdminBrowseOrderGridPercentage                                   |  0.2                | Percentage of threads in Combined Benchmark Pool that emulate orders grid browsing activities.       |
| cAdminProductCreationPercentage                                   |  0.5                | Percentage of threads in Combined Benchmark Pool that emulate product creation activities.           |
| cAdminProductEditingPercentage                                    |  0.65               | Percentage of threads in Combined Benchmark Pool that emulate product editing activities.            |
| cAdminReturnsManagementPercentage                                 |  0.75               | Percentage of threads in Combined Benchmark Pool that emulate admin returns management activities.   |
| cAdminBrowseCustomerGridPercentage                                |  0.1                | Percentage of threads in Combined Benchmark Pool that emulate customers grid browsing activities.    |
| cAdminCreateOrderPercentage                                       |  0.5                | Percentage of threads in Combined Benchmark Pool that emulate creating orders activities.            |
| cAdminCategoryManagementPercentage                                |  0.15               | Percentage of threads in Combined Benchmark Pool that emulate admin category management activities.  |
| cAdminPromotionRulesPercentage                                    |  0.2                | Percentage of threads in Combined Benchmark Pool that emulate admin promotion rules activities.      |
| cAdminCustomerManagementPercentage                                |  0.4                | Percentage of threads in Combined Benchmark Pool that emulate admin customers management activities. |
| cAdminEditOrderPercentage                                         |  1                  | Percentage of threads in Combined Benchmark Pool that emulate admin edit order activities.           |


Parameters must be passed to the command line with the `J` prefix:

`-J{parameter_name}={parameter_value}`

The required parameters are `{host}` and `{base_path}`. All other parameters are optional. If you do not pass any custom value, a default value will be used.

There are some options that you should pass to JMeter in the console mode:

`-n` Run scenario in Non-GUI mode
`-t` Path to the JMX file to be run
`-l` Path to the JTL file to log sample results to
`-j` Path to JMeter run log file

To get more details about available JMeter options, read [Non-GUI Mode](http://jmeter.apache.org/usermanual/get-started.html#non_gui).

For example, you can run the B2C scenario via console with:
90 threads for the Frontend Pool where:
- 80% - guest catalog browsing activities.
- 20% - checkout by customer.
 
10 threads for the Admin Pool where:
- 10% - admin products grid browsing activities. 
- 90% - admin product creation activities.

    cd {JMeter path}/bin/
    jmeter -n -t {path to performance toolkit}/benchmark.jmx -j ./jmeter.log -l ./jmeter-results.jtl -Jhost=magento2.dev -Jbase_path=/ -Jadmin_path=admin -JfrontendPoolUsers=90 -JadminPoolUsers=10 -JbrowseCatalogByGuestPercentage=80 -JcheckoutByCustomerPercentage=20 -JbrowseProductGridPercentage=10 -JadminProductCreationPercentage=90

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

`benchmark.jmx` scenario has the following pools:

**Frontend Pool** (frontendPoolUsers)

**Admin Pool** (adminPoolUsers)

**CSR Pool** (csrPoolUsers)

**API Pool** (apiPoolUsers)

**One Thread Scenarios Pool** (oneThreadScenariosPoolUsers)

**GraphQL Pool** (graphQLPoolUsers)

**Combined Benchmark Pool** (combinedBenchmarkPoolUsers)

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
