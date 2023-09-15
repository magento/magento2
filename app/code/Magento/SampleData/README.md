Magento sample data includes a sample store, complete with more than 250 products (about 200 of them are configurable products), categories, promotional price rules, CMS pages, banners, and so on. Sample data uses the Luma theme on the storefront.

Installing sample data is optional.

Technically, sample data is a set of regular Magento modules, which can be deployed and installed together with the Magento instance, or later in the scope of upgrade.

## Deploy Sample Data

You can deploy sample data from one of the following sources:

* From the Magento composer repository, optionally using Magento CLI
* From the Magento GitHub repository

If your Magento code base was cloned from the mainline branch, you can use either source of the sample data. If it was cloned from the `develop` branch, use the GitHub repository and choose to get sample data modules from the `develop` branch.

### Deploy Sample Data from Composer Repository

To deploy sample data from the Magento composer repository using Magento CLI:

1. If your Magento instance is already installed, skip this step. Otherwise, in the Magento root directory, run: `# composer install`.
2. In the Magento root directory, run: `# bin/magento sampledata:deploy`. This command collects the dependencies from the `suggest` sections of the `composer.json` files of modules, which suggest to install sample data (like `Magento_Catalog`, `Magento_Sales`, and so on).

To deploy sample data from the Magento composer repository without Magento CLI:

1. Specify sample data packages in the `require` section of the root `composer.json` file, for example:

```json
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
```

Where `<version>` is the version of the packages; it should correspond to the version of the Magento instance.

Each package corresponds to a sample data module. The complete list of available modules can be viewed in the [sample data GitHub repository] (https://github.com/magento/magento2-sample-data/tree/develop/app/code/Magento)

2. To update the dependencies, in the Magento root directory, run: `# composer update`

### Deploy Sample Data from GitHub Repository

To deploy sample data from the GitHub repository:

1. Clone sample data from `https://github.com/magento/magento2-sample-data`. If your Magento instance was cloned from the mainline branch, choose the mainline branch when cloning sample data; choose the `develop` branch if Magento was cloned from `develop`.
2. Link the sample data and your Magento instance by running: `# php -f <sample-data_clone_dir>/dev/tools/build-sample-data.php -- --ce-source="<path_to_your_magento_instance>"`

## Install Sample Data

Once the sample data is deployed, it will be installed automatically when you install or upgrade your Magento instance by using the command line.

## Uninstall Sample Data

To remove the sample data modules from the code base, run one of the following commands from the Magento root directory:

* If sample data was deployed from the composer repository, run: `# bin/magento sampledata:remove`
* If sample data was deployed from the GitHub repository and linked to your Magento instance, run:
`# php -f <sample-data_clone_dir>/dev/tools/build-sample-data.php – --command=unlink --ce-source="<path_to_your_magento_instance>"`

To delete all the products and other entities provided by the sample data modules, delete the database and reinstall Magento with a clean database.

## Reinstall Sample Data

If you have deleted certain entities provided by sample data and want to restore them, take the following steps:

1. From the Magento root directory, run the following command: `# bin/magento sampledata:reset`
2. Upgrade Magento as usual.

The deleted sample data entities will be restored. Those entities, which were changed, will preserve these changes and will not be restored to the default view.

## Documentation

You can find the more detailed description of sample data manipulation procedures at <https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/next-steps/sample-data/overview.html>.
