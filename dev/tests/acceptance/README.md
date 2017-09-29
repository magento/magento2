# Magento 2 Functional Tests
 
# Built With
* [Codeception](http://codeception.com/)
* [Robo](http://robo.li/)
* [Allure](http://allure.qatools.ru/)

----

# Prerequisites
* **IMPORTANT**: Configure your Magento Store for [Automated Testing](http://devdocs.magento.com/guides/v2.0/mtf/mtf_quickstart/mtf_quickstart_magento.html)
* [PHP v7.x](http://php.net/manual/en/install.php)
* [Composer v1.4.x](https://getcomposer.org/download/)
* [Java](https://www.java.com/en/download/)
* [Selenium Server](http://www.seleniumhq.org/download/) - [v2.53.x](http://selenium-release.storage.googleapis.com/index.html?path=2.53/)
* [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/downloads)
* [Allure CLI](https://docs.qameta.io/allure/latest/#_installing_a_commandline)
* [GitHub](https://desktop.github.com/)

### Recommendations
* We recommend using [PHPStorm 2017](https://www.jetbrains.com/phpstorm/) for your IDE. They recently added support for [Codeception Test execution](https://blog.jetbrains.com/phpstorm/2017/03/codeception-support-comes-to-phpstorm-2017-1/) which is helpful when debugging.
* We also recommend updating your [$PATH to include](https://stackoverflow.com/questions/7703041/editing-path-variable-on-mac) `./vendor/bin` so you can easily execute the necessary `robo` and `codecept` commands instead of `./vendor/bin/robo` or `./vendor/bin/codecept`.  

----

# TEMPORARY INSTALLATION INSTRUCTIONS
Due to the current setup of the Framework you will need to do the following:

  * `mkdir [DIRECTORY_NAME]`
  * `cd [DIRECTORY_NAME]`
  * Pull down - [EE](https://github.com/magento-pangolin/magento2ee)
  * Pull down - [CE](https://github.com/magento-pangolin/magento2ce)
  * `cd magento2ee`
  * `php -f dev/tools/build-ee.php -- --command=link --exclude=true`
  * Generate a `github-oauth` token: [Instructions](https://help.github.com/articles/creating-a-personal-access-token-for-the-command-line/#creating-a-token)
  * `touch magento2ce/dev/tests/acceptance/auth.json`
  * `nano magento2ce/dev/tests/acceptance/auth.json`
  * Replace `<personal access token>` with the token you generated in GitHub.
  * Save your work.
  * `cd ../magento2ce`
  * `cd dev/tests/acceptance`
  * `composer install`
    * **PLEASE IGNORE THE "Installation" SECTION THAT FOLLOWS, START WITH THE "Building The Framework" SECTION INSTEAD.**

----

# Installation
You can **either** install through composer **or** clone from git repository.
## Git
```
git clone GITHUB_REPO_URL
cd magento2ce
composer install
```

## Composer
```
mkdir DIR_NAME
cd DIR_NAME
composer create-project --repository-url=GITHUB_REPO_URL magento/magento2ce-acceptance-tests-metapackage
```

----

# Robo
Robo is a task runner for PHP that allows you to alias long complex CLI commands to simple commands.

### Example

* Original: `allure generate tests/_output/allure-results/ -o tests/_output/allure-report/`
* Robo: `./vendor/bin/robo allure1:generate`

## Available Robo Commands
You can see a list of all available Robo commands by calling `./vendor/bin/robo` in the Terminal.

##### Codeception Robo Commands
* `./vendor/bin/robo`
  * Lists all available Robo commands.
* `./vendor/bin/robo clone:files`
  * Duplicate the Example configuration files used to customize the Project
* `./vendor/bin/robo build:project`
  * Build the Codeception project
* `./vendor/bin/robo generate:pages`
  * Generate all Page Objects
* `./vendor/bin/robo generate:tests`
  * Generate all Tests in PHP
* `./vendor/bin/robo example`
  * Run all Tests marked with the @group tag 'example', using the Chrome environment
* `./vendor/bin/robo chrome`
  * Run all Functional tests using the Chrome environment
* `./vendor/bin/robo firefox`
  * Run all Functional tests using the FireFox environment
* `./vendor/bin/robo phantomjs`
  * Run all Functional tests using the PhantomJS environment
* `./vendor/bin/robo folder ______`
  * Run all Functional tests located under the Directory Path provided using the Chrome environment
* `./vendor/bin/robo group ______`
  * Run all Tests with the specified @group tag, excluding @group 'skip', using the Chrome environment
  
##### Allure Robo Commands
To determine which version of the Allure command you need to use please run `allure --version`.

* `./vendor/bin/robo allure1:generate`
  * Allure v1.x.x - Generate the HTML for the Allure report based on the Test XML output
* `./vendor/bin/robo allure1:open`
  * Allure v1.x.x - Open the HTML Allure report
* `./vendor/bin/robo allure1:report`
  * Allure v1.x.x - Generate and open the HTML Allure report
* `./vendor/bin/robo allure2:generate`
  * Allure v2.x.x - Generate the HTML for the Allure report based on the Test XML output
* `./vendor/bin/robo allure2:open`
  * Allure v2.x.x - Open the HTML Allure report
* `./vendor/bin/robo allure2:report`
  * Allure v2.x.x - Generate and open the HTML Allure report

----

# Building The Framework
After installing the dependencies you will want to build the Codeception project in the [Magento 2 Functional Testing Framework](https://github.com/magento-pangolin/magento2-functional-testing-framework), which is a dependency of the CE or EE Tests repo. Run `./vendor/bin/robo build:project` to complete this task.

`./vendor/bin/robo build:project`

----

# Configure the Framework
Before you can generate or run the Tests you will need to edit the Configuration files and configure them for your specific Store settings. You can edit these files with out the fear of accidentally committing your credentials or other sensitive information as these files are listed in the *.gitignore* file.

In the `.env` file you will find key pieces of information that are unique to your local Magento setup that will need to be edited before you can generate tests:
* **MAGENTO_BASE_URL**
* **MAGENTO_BACKEND_NAME**
* **MAGENTO_ADMIN_USERNAME**
* **MAGENTO_ADMIN_PASSWORD**

##### Additional Codeception settings can be found in the following files: 
* **tests/functional.suite.yml**
* **codeception.dist.yml**

----

# Generate PHP files for Tests
All Tests in the Framework are written in XML and need to have the PHP generated for Codeception to run. Run the following command to generate the PHP files in the following directory (If this directory does not exist it will be created): `dev/tests/acceptance/tests/functional/Magento/FunctionalTest/_generated`

`./vendor/bin/robo generate:tests`

----

# Running Tests
## Start the Selenium Server
**PLEASE NOTE**: You will need to have an instance of the Selenium Server running on your machine before you can execute the Tests.

```
cd [LOCATION_OF_SELENIUM_JAR]
java -jar selenium-server-standalone-X.X.X.jar
```

## Run Tests Manually
You can run the Codeception tests directly without using Robo if you'd like. To do so please run `./vendor/bin/codecept run functional` to execute all Functional tests that DO NOT include @env tags. IF a Test includes an [@env tag](http://codeception.com/docs/07-AdvancedUsage#Environments) you MUST include the `--env ENV_NAME` flag.

#### Common Codeception Flags:

* --env
* --group
* --skip-group
* --steps
* --verbose
* --debug
  * [Full List of CLI Flags](http://codeception.com/docs/reference/Commands#Run)

#### Examples

* Run ALL Functional Tests without an @env tag: `./vendor/bin/codecept run functional`
* Run ALL Functional Tests without the "skip" @group: `./vendor/bin/codecept run functional --skip-group skip`
* Run ALL Functional Tests with the @group tag "example" without the "skip" @group tests: `./vendor/bin/codecept run functional --group example --skip-group skip`

## Run Tests using Robo
* Run all Functional Tests using the @env tag "chrome": `./vendor/bin/robo chrome`
* Run all Functional Tests using the @env tag "firefox": `./vendor/bin/robo firefox`
* Run all Functional Tests using the @env tag "phantomjs": `./vendor/bin/robo phantomjs`
* Run all Functional Tests using the @group tag "example": `./vendor/bin/robo example`
* Run all Functional Tests using the provided @group tag: `./vendor/bin/robo group GROUP_NAME`
* Run all Functional Tests listed under the provided Folder Path: `./vendor/bin/robo folder dev/tests/acceptance/tests/functional/Magento/FunctionalTest/MODULE_NAME`

----

# Allure Reports
### Manually
You can run the following commands in the Terminal to generate and open an Allure report.

##### Allure v1.x.x
* Build the Report: `allure generate tests/_output/allure-results/ -o tests/_output/allure-report/`
* Open the Report: `allure report open --report-dir tests/_output/allure-report/`

##### Allure v2.x.x
* Build the Report: `allure generate tests/_output/allure-results/ --output tests/_output/allure-report/ --clean`
* Open the Report: `allure open --port 0 tests/_output/allure-report/`

### Using Robo
You can run the following Robo commands in the Terminal to generate and open an Allure report (Run the following terminal command for the Allure version: `allure --version`):

##### Allure v1.x.x
* Build the Report: `./vendor/bin/robo allure1:generate`
* Open the Report: `./vendor/bin/robo allure1:open`
* Build/Open the Report: `./vendor/bin/robo allure1:report`

##### Allure v2.x.x
* Build the Report: `./vendor/bin/robo allure2:generate`
* Open the Report: `./vendor/bin/robo allure2:open`
* Build/Open the Report: `./vendor/bin/robo allure2:report`

----

# Composer SymLinking
Due to the interdependent nature of the 2 repos it is recommended to Symlink the repos so you develop locally easier. Please refer to this GitHub page: https://github.com/gossi/composer-localdev-plugin

----

# Troubleshooting
* TimeZone Error - http://stackoverflow.com/questions/18768276/codeception-datetime-error
* TimeZone List - http://php.net/manual/en/timezones.america.php
* System PATH - Make sure you have `./vendor/bin/`, `vendor/bin/` and `vendor/` listed in your system path so you can run the  `codecept` and `robo` commands directly:

    `sudo nano /etc/paths`
    
* StackOverflow Help: https://stackoverflow.com/questions/7703041/editing-path-variable-on-mac
* Allure `@env error` - Allure recently changed their Codeception Adapter that breaks Codeception when tests include the `@env` tag. A workaround for this error is to revert the changes they made to a function. 
    * Locate the `AllureAdapter.php` file here: `vendor/allure-framework/allure-codeception/src/Yandex/Allure/Adapter/AllureAdapter.php`
    * Edit the `_initialize()` function found on line 77 and replace it with the following:
```
public function _initialize(array $ignoredAnnotations = [])
    {
        parent::_initialize();
        Annotation\AnnotationProvider::registerAnnotationNamespaces();
        // Add standard PHPUnit annotations
        Annotation\AnnotationProvider::addIgnoredAnnotations($this->ignoredAnnotations);
        // Add custom ignored annotations
        $ignoredAnnotations = $this->tryGetOption('ignoredAnnotations', []);
        Annotation\AnnotationProvider::addIgnoredAnnotations($ignoredAnnotations);
        $outputDirectory = $this->getOutputDirectory();
        $deletePreviousResults =
            $this->tryGetOption(DELETE_PREVIOUS_RESULTS_PARAMETER, false);
        $this->prepareOutputDirectory($outputDirectory, $deletePreviousResults);
        if (is_null(Model\Provider::getOutputDirectory())) {
            Model\Provider::setOutputDirectory($outputDirectory);
        }
    }
```
