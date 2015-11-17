# Introduction

Magento sample data uses the responsive Luma theme to display a sample store, complete with products, categories, promotional price rules, CMS pages, banners, and so on. You can use the sample data to come up to speed quickly and see the power and flexibility of the Magento storefront.

Installing sample data is optional; you can install it before or after you install the Magento software.

# Deployment

Deployment of Sample Data can be performed in three ways: using Magento CLI, composer and from GitHub repository.

## Using CLI

SampleData module is included to default scope of Magento CE modules. To deploy other sample data modules (e.g. ConfigurableSampleData, CatalogSampleData e.t.c) Magento CLI command can be used:

```
# bin/magento sampledata:deploy
```

CLI command collects suggest node from composer.json files of modules which suggest to install sample data:

```
"suggest": {
  "magento/module-catalog-sample-data": "Sample Data version:1.0.0-beta"
}
```

## Using Composer

Also it's possible to add needed sample data modules manually (via composer require or editing main composer.json file)

1. Specify packages
```
{
    "require": {
    ...
       "magento/module-catalog-sample-data": "{version}",
        "magento/module-configurable-sample-data": "{version}",
        "magento/module-cms-sample-data": "{version}",
        "magento/module-sales-sample-data": "{version}"
        ....
    }
 }
{version} - Go to repo.magento.com and write down suitable versions of magento/sample-data and magento/sample-data-media (typically, you should choose the most recent version).
```
2. Run composer update from your Magento root directory

## From GitHub Repository

1. Clone Sample Data from https://github.com/magento/magento2-sample-data
2. Link Sample Data and Magento Edition using tool <sample-data-ce-root>/dev/tools/build-sample-data.php
```
php -f <sample-data-ce-root>/dev/tools/build-sample-data.php -- --ce-source="path/to/magento/ce/edition"
```

# Installing

Being once deployed the Sample Data is available for installation through the Magento Setup Wizard or using CLI.

## Web Installation

Deployed SampleData modules have been selected to be installed by default. To prepare installation Magento with SampleData finish installation as you do it usual. In success page user will be notified about successfully installed SampleData

## Console Installation

Use Magento CLI installation command to install Magento as usual.

# Uninstalling

There is CLI command which allows to remove all sample data modules from Magento (only for case when sample data was installed via composer):

```
# bin/magento sampledata:remove
```

# Re-installation

To prepare sample data for re installation process run:

```
# bin/magento sampledata:reset
```

Then install or upgrade Magento as usual
