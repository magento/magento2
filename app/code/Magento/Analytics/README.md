## Glossary

|Term|Meaning|Description|
|--- |--- |--- |
|MBI|Magento Business Intelligence -|SaaS reporting service|
|Advance Reporting||The free MBI service for CE customers. User can open the Advance Reporting page from Magento|
|BI Essentials||The paid MBI service for CE customers. User can open the BI Essentials page from Magento|
|DD|Data Definition|The file with a list of data to be transferred to MBI service. This data after transformation to reports is shown on Advanced Reporting page|
|MBIIM|MBI Integration Module|Set of CE modules to enable integration with MBI|
|External Data Definitions||Data definitions which Magento gets dynamically from MBI service|
|Allowed Data||The data configuration inside Magento with allowance to be sent to external reporting service|
|OTP|One-Time Password|Unique Key to get authorized access to Advance Reporting page|

## Overview
  The main purpose of this document is to describe the way MA can be integrated with M2. This document describes the work that needs to be done in the scope of Free Tier project.
  
## Integration Module Architecture
### Module Structure
*   Analytics
    *   Provides subscription and restore subscription procedures
    *   Declare the configuration of collected data
    *   Process the data collection
    *   Introduce API for transferring the collected data to MBI service
    *   ACL 
    *   Configuration page  

*   CustomerAnalytics 
    *   Configure the data definition for data collection related to Customer module entities  

*   SalesAnalytics: 
    *   Configure data definitions for data collection to Sales module entities
    
    ![Analytics Modules](./docs/images/analytics_modules.png)
    
### Data Interchange
#### Subscription
   ![Subscription](./docs/images/signup.png)

#### Subscription Update
   ![Subscription Update](./docs/images/update.png)

### Request the External Data Definitions (TBD)
   ![Request the External Data Definitions(TBD)](./docs/images/definition.png)

### Data Transition
   ![Data Transition](./docs/images/data_transition.png)

## Report MXL
### Overview
**Report XML** - markup language for building Analytics reports. Based on XML. XML like SQL declarative language. It is easy to process and validate.
Third party developer can retrieve data using a report name. Report name is the same as attribute `name` in &lt;report&gt; node as described below. The `getReport` method of report provider returns the object that implements Iterator Interface.
### Creating a new report
Report files have to be located in modules_name/etc/reports.xml.
Report files can be located in any custom modules that depends on Analytics (e.g. SalesAnalytics module created for Sales related reports). Each report is declared in &lt;report&gt;  node inside node &lt;config&gt; . One report node will render into one SQL query. 
&lt;config&gt; contains the following attributes:

|Name|Description|Is required?|
|--- |--- |--- |
|name|name of report|True|
|connection|name of the connection to DB|False|
|iterator|full class name to statement iterator|False|

All data of Reports.xml from node &lt;report&gt; which have the same attribute name will be merged. The Magento store can have more than one DB. Therefore, we should specify a connection name using the 'connection' attribute.
You can use a custom iterator to modify or filter data. To use a custom iterator, the `iterator` attribute must contain iterator class or interface name. This iterator can get statement iterator in the constructor method and wrap or change current values with custom data.

Example of reports.xml:
```
<?xml version="1.0"?>
   <!--
   /**
    * Copyright © 2013-2017 Magento, Inc. All rights reserved.
    * See COPYING.txt for license details.
    */
   -->
   <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Analytics:etc/reports.xsd">
       <report name="modules" connection="default" iterator="Magento\Analytics\Model\ReportXml\ModuleIterator">
           <source name="setup_module">
               <attribute name="module" alias="module_name"/>
               <attribute name="schema_version"/>
               <attribute name="data_version"/>
           </source>
       </report>
       <report name="config_data" connection="default">
           <source name="core_config_data">
               <attribute name="path"/>
               <attribute name="value"/>
           </source>
       </report>
   </config>
```
### Data sources
We describe data sources inside a node report which are equal to the table names in DB. The main table is specified with the tag &lt;source&gt;. After rendering it will be represented as FROM statement in SQL query.
Node &lt;source&gt; contains  the following attributes:

|Name|Description|Is required?|
|--- |--- |--- |
|name|name of table|True|
|alias|alias of table|False|

The name has to be equivalent to the table name in DB. The alias attribute can be used in the same way as an alias in the SQL.
In the source node, we can also add additional data source with the tag &lt;link-source&gt;. After rendering it will be represented as JOIN statement in SQL query.
Node &lt;link-source&gt; contains  the following attributes:

|Name|Description|Is required?|
|--- |--- |--- |
|name|name of table|True|
|alias|alias of table|False|
|link-type|type of join|False|

The name has to be equivalent to the table name in DB. The alias attribute can be used in the same way as an alias in the SQL. The link-type attribute specifies the type of join in SQL query. It can be INNER or LEFT.
Join conditions are described in node &lt;link-source&gt; with the tag &lt;using&gt;. After rendering it will be represented as ON statement in SQL query. &lt;using&gt; works in the same way as the filter which will be described below.

**Example of orders datasource in reports.xml**
```
<report name="orders" connection="sales">
    <source name="sales_order" alias="sales">
        <attribute name="entity_id"/>
        <attribute name="base_grand_total"/>
        <attribute name="base_tax_amount"/>
        <attribute name="base_shipping_amount"/>
        <attribute name="coupon_code"/>
        <attribute name="created_at"/>
        <attribute name="store_id"/>
        <attribute name="email"/>
        <link-source name="sales_order_address" alias="billing" link-type="left">
            <attribute name="email"/>
            <using glue="and">
                <condition attribute="parent_id" operator="eq" type="identifier">entity_id</condition>
                <condition attribute="address_type" operator="eq" type="value">billing</condition>
            </using>
        </link-source>
    </source>
</report>
```
### Report columns
Report XML does not support the asterisk statement. All needed columns should be declared inside &lt;source&gt; xml node for main table and inside &lt;link-source&gt; for joined tables. Columns are added with the tag <attribute>.
Node &lt;attribute&gt; contains  the following attributes:

|Name|Description|Is required?|
|--- |--- |--- |
|alias|alias of column|False|
|name|name of column|True|

The name has to be equivalent to the column name in DB. The column alias attribute can be used in the same way as a column alias in the SQL.
Additional columns can be added through custom iterator declaration as described in first section [Creating a new report](#creating-a-new-report).

### Report filters
The report can be filtered using the &lt;filter&gt; tag. Filters are declared inside &lt;source&gt; node. Filters have attribute glue. Glue is used to filter records based on more than one condition. Glue can have type __OR__ or __AND__. Glue type is __AND__ by default.
The node filter can have nested filters or/and &lt;conditions&gt;.
**Example of nested condition in SQL:**
```
WHERE ((billing.entity_id IS NULL AND ((billing.entity_id < '200' AND billing.entity_id != '42') AND (billing.entity_id > '200' OR billing.entity_id != '201'))))
```
**Example of nested condition in Report XML:**
```
<filter glue="and">
    <condition attribute="entity_id" operator="null" />
    <filter glue="and">
        <condition attribute="entity_id" operator="lt">200</condition>
        <condition attribute="entity_id" operator="neq">42</condition>
    </filter>
    <filter glue="or">
        <condition attribute="entity_id" operator="gt">200</condition>
        <condition attribute="entity_id" operator="neq">201</condition>
    </filter>
</filter>
```
Node &lt;conditions&gt; contains the following attributes:

|Name|Description|Is required?|
|--- |--- |--- |
|attribute|name of column|True|
|type|type of comaprsion value|false|
|operator|comparison operator|True|

The attribute has to be equivalent to the column name in DB. The attribute type can be value or identifier. In case type is identifier value inside condition is the column. In case type is value it means is the scalar value. By default, the type is the value.
Operator describes which comparison operator will be used to compare columns with the value or columns that can be specified inside &lt;conditions&gt; xml node.
All supported comparison operators can be found in \Magento\Analytics\ReportXml\DB\ConditionResolver::$conditionMap.

## Access Rights
There are two ACL (Access Control List) resources introduced for MBIIM purposes:
*   'Analytics\API' - permission to download the data archive
*   'Stores\Settings\Configuration\Analytics' - permission to manage (enable/disable) subscription on configuration page

### Download the data archive
MA service has to use Magento API for downloading the data archive. To make it possible Magento instance creates a special admin user during an installation process. The user has permissions to access the data archive via API only and cannot perform any other actions.
Please note, that it is strictly not recommended to remove the user or edit their ACL.
### Manage the subscription
To be able to manage MA subscription, an admin user has to have corresponding permissions. For this case, the user can change the status of the subscription in a popup or from a corresponding configuration section.

## Subscription to Advanced Analytics
*   MBIIM installs new service user and ACL to access API
*   Automatically created service user can not access to Magento admin panel directly
*   Service user can perform calls to API which retrieve previously collected data
*   Advanced Reporting provides Subscription API
*   The Subscription  API receives Magento 2 connection token
*   The Subscription  API returns token for future access to Magento Analytics services
*   Cron enabled and configured for Magento
#### Precondition
*   MAFT installs new user and ACL to access API
*   Automatically created user can not access to Magento admin panel directly
*   User can perform calls to API which retrieve previously collected data
*   Magento Analytics provides sign-up API
*   The sign-up API receives Magento 2 connection token
*   The sign-up API returns token for future access to Magento Analytics services
*   Cron enabled and configured for Magento
#### Design
*   After Free Tier module has been installed, the merchant can enable subscription 
*   M2 generates new access token for API user and sends it to MA signup service
*   MA returns its own access token
*   Received token stored in Magento System Configuration
*   Subscription is applied to all merchant`s websites and stores

#### SignUp
   ![SignUp](./docs/images/M2_MA_signup.png)

**SignUp payload:**
```
{
   "token": "fc7bf8e3c68c2c1f8da9b53bfa62e7ff9ffb10f3",
   "url": "demo-store.magento.com"
}
```
#### Exception Handling
*   In case of Magento did not receive MA access token or sign-up service returned error message - Magento is trying to repeat the API sign-up call  to MA service
*   Interval for calls SignUp command and the number of attempts is pre-configured in module

### Sign-up pop-up window
As a Merchant, I want to confirm sending of my system configuration and transaction data to the Magento Analytics (MA) service so that I am getting back the reports improved the look and vertical bench-marking.
The confirmation modal window will be shown every seven days until the 'OK' button is pressed (regardless of whether checkbox is checked or not). Click on 'OK' button calls '\Magento\Analytics\Model\NotificationTime::unsetLastTimeNotificationValue()' which deletes 'notification_time' flag from 'flag' DB table. This flag is the only condition to display the Confirmation Window:
*   the flag exists in the DB table
*   period after the last notification (time stamp recorded in 'flag_data' attribute) is more than 7 days (in seconds)

The confirmation modal window is added to content of 'app/code/Magento/Analytics/view/adminhtml/layout/adminhtml_dashboard_index.xml' layout:
```
<body>
        <referenceContainer name="content">
            <uiComponent name="analytics_subscription_form" acl="Magento_Analytics::analytics_settings" condition="analytics::can-view-notification" />
        </referenceContainer>
</body>
```
A subscription form is added as a UI component 'app/code/Magento/Analytics/view/adminhtml/ui_component/analytics_subscription_form.xml'. The form contains two actions 'OK' and 'Cancel' that call controllers '\Magento\Analytics\Controller\Adminhtml\Subscription\Activate' and '\Magento\Analytics\Controller\Adminhtml\Subscription\Postpone' respectively.
Activate controller enables subscription through a '\Magento\Analytics\Model\Subscription' service. The service sets 'default/analytics/subscription/enabled' config to "true" that triggers '\Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler::process()' and sets a cron job (refer to 'app/code/Magento/Analytics/etc/crontab.xml').
Postpone controller registers time of the most recent notification which is being used by cron to calculate the time of the next notification.

### Config Management
*   After merchant confirmed subscription Magento stores information that subscription was enabled into config setting
*   Magento creates cron to call MA sign-up service
*   In case of success, cron writes received token into config setting
*   In case of failure cron decrements numbers of allowed attempts and schedule next run, max allowed number of attempts is 24
*   Merchant can trigger subscription directly by using button Subscribe at config page

The merchant can unsubscribe from MA services. M2 does not perform any additional calls to MA.

### SignUp process sequence
1. Admin enables subscription in a pop-up or in General > Analytics section of Admin area. At this point, \Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler creates and saves cron expression.
2. Magento parses \Magento\Analytics\etc\crontab.xml that contains following declaration in cronjob.xml:
```
<group id="default">
<job name="analytics_subscribe" instance="Magento\Analytics\Cron\SignUp" method="execute" />
</group>
```
3. Builds cron for this cron job from db.
4. \Magento\Analytics\Cron\SignUp runs 'SignUp' command from commands pool which is contained in \Magento\Analytics\Model\AnalyticsConnector.
5. If 'SignUp' command executed successfully \Magento\Analytics\Cron\SignUp stops further executions of the command by removing cron expression created at step #1.
6. If 'SignUp' command failed \Magento\Analytics\Cron\SignUp tries to run the command (once in an hour) while it is not executed successfully or while the number of retries didn't reach the number of allowed attempts.

The number of allowed attempts is stored in the flag \Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE.

### SignUpCommand responsibilities
*   Decide to retrieve from AnalyticsApiProvider or generate ApiUserToken
*   Call analytics service for signup
*   Sets analytics token into AnalyticsTokenModel
*   Logs errors
*   Checks response status

### Sign-Up Retry
*   In case after sign-up process a subscription status is failed (look for [Subscription Statuses](#subscription-statuses) below) a user will get a notification.  
*   This message contains text that the subscription process has failed and a retry link. The link allows executing a process of retry subscription - start a sign-up process again.
*   The notification is shown all time on all pages in admin panel if a subscription status is failed.

## Cancel/Restore the Subscription
### Overview
Subscription for Magento Business Intelligence (MBI) service may be canceled (disabled) or restored (enabled) at any time in 'Stores > Configuration > General > Analytics' section of Admin area.
When configuration value is changed, '\Magento\Analytics\Model\Config\Backend\Enabled::afterSave()' use '\Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler' service for processing of activation/deactivation MBI subscription.
Magento receives the MBI token only once during the first success sign-up request. Moreover, the MBI token will not be removed if the subscription is canceled (disabled).
So there will not be additional sign-up request when the subscription is restored (enabled) after cancellation. Please note that '\Magento\Analytics\Model\AnalyticsToken' class is responsible for all necessary operations with the MBI token (set/get value, check if the token exists).

### Subscription statuses
Subscription may be in several statuses. Magento considers subscription as **'enabled'**, **'pending'**, **'failed'** or **'disabled'** by checking configuration parameters:
*   'default/analytics/subscription/enabled', boolean, located in 'core_config_data' DB table - state of the subscription
*   'analytics/general/token', string, located in the 'core_config_data' DB table - MBI token
*   'analytics_link_attempts_reverse_counter', integer, located in 'flag' DB table - reserve counter of attempts to subscribe

Thus, subscription is considered as **'enabled'** if:
*   'default/analytics/subscription/enabled' is **TRUE**
*   'analytics/general/token' is **present and not empty**

Subscription is considered as **'pending'** if:
*   'default/analytics/subscription/enabled' is **TRUE**
*   'analytics/general/token' is **absent or empty**

Subscription is considered as **'failed'** if:
*   'default/analytics/subscription/enabled' is **TRUE**
*   'analytics/general/token' is **absent or empty**
*   'analytics_link_attempts_reverse_counter' is **absent**

Subscription is considered as **'disabled'** if:
*   'default/analytics/subscription/enabled' is **FALSE**, regardless of 'analytics/general/token'

Refer to '\Magento\Analytics\Model\SubscriptionStatusProvider' class which is responsible for determining a subscription status.

## Update of a subscription for the Advanced Analytics
  Since the Advanced Analytics service identifies Magento instances by a combination of a secure base URL and a unique token, the Magento team has implemented a subscription update mechanism in order to keep synchronization with the Advanced Analytics service in the case when secure base URL of a store was changed.
  The mechanism consists of two main elements - the plugin and the cron job.
 
### The Plugin
  The main mission of the plugin is to detect changes of a secure base URL of a store. In case if such changes were detected and a Magento instance is already subscribed for the Advanced Analytics service, the plugin creates a corresponding cron job in order to schedule sending of an 'Update' request.

**A fragment of the 'app/code/Magento/Analytics/etc/di.xml' file:**
 ```
 <config>
     ...
     <type name="Magento\Config\Model\Config\Backend\Baseurl">
         <plugin name="updateAnalyticsSubscription" type="Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin" />
     </type>
     ...
 </config>
 ```
### The Cron Job
The cron job performs a corresponding request in order to notify the Advanced Analytics service of a secure base URL change. The job will be executed every hour until the notification is successful.
 
**A fragment of the 'app/code/Magento/Analytics/etc/crontab.xml' file:**
 ```
 <group id="default">
     ...
     <job name="analytics_update" instance="Magento\Analytics\Cron\Update" method="execute" />
     ...
 </group>
 ```
### The Update Request
  ![Update Request](./docs/images/update_request.png)
 
An 'Update' request is a **PUT HTTP request** which contains data in  **JSON**  format. The endpoint for the request is configured in the 'app/code/Magento/Analytics/etc/config.xml' file.
 The request is considered successful only in case if Advanced Analytics service responded with a **201 HTTP status.**

**An example of an 'Update' request body**
 ```
 {
    "url": "https://old.magento.com",
    "new-url": "https://new.magento.com",
    "access-token": "fc7bf8e3c68c2c1f8da9b53bfa62e7ff9ffb10f3"
 }
 ```
 Note that the example above contains a complete list of allowed parameters. All these parameters are required.
 
## Subscription to BI Essentials
 
 The Integration Modules provides the link to open external service BI Essentials.
 The link presents in Main Menu in **Reports** section.
 
## Data collection and transition to MBI
 
### Configuration of data collection
A Magento instance collects data that will be taken by the Magento Business Intelligence (MBI) service for building advanced reports. To make it possible to configure data collection, new configuration file '/etc/analytics.xml' was introduced.
The file describes which information will be collected and how it will be distributed among data sets (files). Let's take a look at an example of such file.

**An example of a '/etc/analytics.xml' file**
```
<?xml version="1.0"?>
<!--
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Analytics:etc/analytics.xsd">
    <file name="modules">
        <providers>
            <reportProvider name="modules" class="Magento\Analytics\ReportXml\ReportProvider">
                <parameters>
                    <name>modules</name>
                </parameters>
            </reportProvider>
        </providers>
    </file>
</config>
```
The example shown above describes the following:
*   A data file named 'modules.csv' has to be included into a data archive prepared for the MBI service
*   The file has to be filled with data provided by the '\Magento\Analytics\ReportXml\ReportProvider' class
*   The class has to provide data according to definition of the 'modules' report (see '/etc/reports.xml')

Note that the '\Magento\Analytics\Model\ReportWriter' class is responsible for a decision about a data file extension ('.csv', '.json', etc.).
Configuration of data collection may be extended or changed in any module by adding a corresponding '/etc/analytics.xml' file.

#### The Structure
In accordance with the 'app/code/Magento/Analytics/etc/analytics.xsd' schema the file may have the following structure.

**A structure of a '/etc/analytics.xml' file:**
```
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Analytics:etc/analytics.xsd">
    <file name="file_name_1" prefix="file_name_1_prefix">
        <providers>
            <reportProvider name="provider_name_1" class="Path\To\TheFirst\Provider">
                <parameters>
                    <name>report_name_1</name>
                </parameters>
            </reportProvider>
            <customProvider name="provider_name_2" class="Path\To\TheFirst\Custom\Provider">
            ...
        </providers>
    </file>
    ...
</config>
```
**Note that:**
*   The &lt;prefix&gt; attribute of the node &lt;file&gt; is optional
*   The node &lt;providers&gt; has to contain at least one node &lt;reportProvider&gt; or &lt;customProvider&gt;

### Notify MBI that data collection is done
Advanced Analytics service cron job which collecting data makes a call to notify MBI when data collection was finished.

#### Request URL declaration
Request URL is declared in config.xml in Analytics module. Config value stored in the config storage path (XML path or DB field in core_config_data table) 'default/analytics/url/notify_data_changed'.

#### Request format
Method: POST

Headers: Content-Type: application/json

Body: {"access-token": "mbi-user-secret-token", "url": "secure-url-to-magento-store"}

#### Response
MBI does not provide any response which can be used in Magento.

## File downloading for MBI
  Advanced Analytics service provides API for pull link and initialization vector.
  
#### Request and Responses through API

**Request and response when file prepared already.**

|Request|Response|
|--- |--- |
|/rest/V1/analytics/link|HTTP_CODE: 200 OK;  BODY: {"url":"http://magento.dev/pub/media/analytics/jsldjsfdkldf/data.tgz","initialization_vector":"base64encodedData"}|

**Request and response when file not prepared yet.**

|Request|Response|
|--- |--- |
|/rest/V1/analytics/link|HTTP_CODE: 404 Not Found; BODY: {"message":"File is not ready yet."}|

**Request and response when 'HTTP' instead of secure 'https' was used.**

|Request|Response|
|--- |--- |
|/rest/V1/analytics/link|HTTP_CODE: 400 Bad request; BODY: {"message":"Operation allowed only in HTTPS"}|

**Request and response when authorization was failed.**

|Request|Response|
|--- |--- |
|/rest/V1/analytics/link|HTTP_CODE: 401 Unauthorized;  BODY:{"message":"Consumer is not authorized to access %resources","parameters":{"resources":"Magento_Analytics::analytics_api"}}|

#### File lifecycle
 ![File lifecycle](./docs/images/mbi_files_exchange.png)
 
#### File decoding
 The file could be decoded in any tools that support algorithm from section "Encription of collected data" below.
  
#### Web API declaration
In below declaration, we declare service with the secure flag. It means that we allow only https connections to this service. Also, API user must have permissions to analytics API.
```
<route url="/V1/analytics/link" method="GET" secure="true">
    <service class="Magento\Analytics\Api\LinkProviderInterface" method="get"/>
    <resources>
        <resource ref="Magento_Analytics::analytics_api" />
    </resources>
</route>
```

## Security

### Magento Advanced Reporting Authorization
We have implemented the button on Magento Dashboard which allows redirecting the user from Magento admin panel to Magento Advanced Reporting. In this case, we have to identify user definitely on Magento Advanced Reporting. So we are using the next steps into the scenario for authorization:
*   Receiving OTP
*   Direct request

We are expecting that this API will be implemented on MBI side.

#### OTP Receiving Process
HTTP Method: POST

**OTP request:**
```
{
   "token": "fc7bf8e3c68c2c1f8da9b53bfa62e7ff9ffb10f3"
}
```
This token was received before from MBI on SignUp step.

**OTP responce:**
```
{
   "otp": "7957645e18ad4d5a249e6b658877bde2a77bc4ab"
}
```
OTP have to be a unique password which can be used only for one authorization. In addition, if this password will be valid for a small period of time (30 seconds for example) it will be better.


#### Authorization Process
HTTP Method: GET

**Direct request to Magento Advanced Reporting**
```
https://dashboard.rjmetrics.com/v2/magento/report?otp=7957645e18ad4d5a249e6b658877bde2a77bc4ab
```
On this action, MBI has to initiate new user session using a standard mechanism like a cookie.

### Encryption of collected data
The main purpose of this document is to describe the way how to save the collected data on a disk without the risk of unauthorized access. 
Considering the fact that MBI can initialize data request within business time to avoid long server load, which is required for data collecting process, collecting data on schedule while downtime is more preferable.
 
#### Data encryption
Collected data are stored as an encrypted archive with files. This archive is encrypted by OpenSSL toolkit and as a result, it is protected from unauthorized access. The archive name is data.tgz.
For this purpose, OpenSSL extension of PHP is used, which is required for Magento, specifically openssl_encrypt() function. Data are encrypted with the method 'AES-256-CBC' with a predefined 256-bit password and each time generated by openssl_random_pseudo_bytes() Initialization Vector. 

The password is an SHA-256 hash from MBI token, which has been received on the sign-up step.
This process requires RAM memory usage equal to 2x of the file size which has to be encrypted. Time on encryption is about 1-2 sec for a file with the size of 500MB.

The encrypted archive is stored into a directory with unique (each time generated hash) name in subdirectory "analytics" of the Magento MEDIA directory (for example "pub/media/analytics/e87d905d4c0d2982ce1fdfc5464e1869c3c35008f4cbdc1c7429bace1b41df84/data.tgz"). It allows providing consistency data for MBI all time.
The Initialization Vector and the relative path to encrypted file are stored into a FileInfo object. Data from the FileInfo object is stored in Magento database as a flag (with flag code "analytics_file_info") and can be restored from the database into the FileInfo object using a FileInfoManager.

#### Schedule the data gathering
Start time of data collection process can be set in 'Stores > Configuration > General > Business Intelligence' section of Admin area as 'Cron configuration > Time' config value.
If the subscription to MBI is enabled, every day, at scheduled time data collection is done.

#### Data decryption
Data can be downloaded in encrypted form according to file [File downloading for MBI](#file-downloading-for-mbi)
Data can be decrypted by MBI with a predefined password and received Initialization Vector. The password is an SHA-256 hash from MBI token which was connected with a current Magento instance.
Initialization Vector is provided to MBI in response in a base64 encoded form.

### Protection of Web API calls against brute-force attacks (TBD)
In order to protect Web API calls against brute force attacks, it is proposed to implement a mechanism of blocking of suspicious IP addresses.
The general idea of the mechanism is to temporary block any IP address after a certain number of failed authorization attempts.

#### Technology
We assume that consumers of Web API are applications. In this case, there is no need to count failed authorization attempts because we do not expect casual input mistakes regularly made by human beings. Thus, it is proposed to block IP addresses for 1 hour (time frame may be discussed) immediately after the first fail.
In terms of Magento, it is proposed to:
*   Introduce described mechanism in the existing WebApi module;
*   Use plugins for appropriate public methods (needs further investigation) which will be responsible for the detection of failed authorization attempts, blocking of IP addresses and checking whether an IP addressis in the black list.

### Summary
Unfortunately, described approach does not provide complete protection against brute-force attacks. Nevertheless, it is able to slow and significantly reduce the efficiency of such kind of threats.
