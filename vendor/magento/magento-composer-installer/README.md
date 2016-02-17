# Magento Composer Installer

This is a fork of the [Magento Composer Installer](https://github.com/magento-hackathon/magento-composer-installer) repo that provides support for Magento 2 components (modules, themes, language packages, libraries and components).

## Usage

In the component's `composer.json`, specify:

*   `type`, type of Magento 2 component.
*   `extra/map`, list of files to move and their paths relative to the Magento root directory.

    **Note**: `extra/map` is required only if your component needs to be moved to a location other than `<Magento root>/vendor`. Otherwise, omit this section.

## Supported Components
The following list explains the use of `type` in `composer.json`.

### Magento Module 
`"type": "magento2-module"`

Installation location: Default vendor directory or as defined in `extra/map`

Example:

```json
{
    "name": "magento/module-core",
    "description": "N/A",
    "require": {
        ...
    },
    "type": "magento2-module",
    "extra": {
        "map": [
            [
                "*",
                "Magento/Core"
            ]
        ]
    }
}
```

Final location is `<magento root>/app/code/Magento/Core`

### Magento Theme 
`"type": "magento2-theme"`

Installation location: `app/design`

Example:
```json
{
    "name": "magento/theme-frontend-luma",
    "description": "N/A",
    "require": {
        ...
    },
    "type": "magento2-theme",
    "extra": {
        "map": [
            [
                "*",
                "frontend/Magento/luma"
            ]
        ]
    }
}
```

Final location is `<magento_root>/app/design/frontend/Magento/luma`

### Magento Language Package
`"type": "magento2-language"`

Installation location: `app/i18n`

Example:
```json
{
    "name": "magento/language-de_de",
    "description": "German (Germany) language",
    "require": {
        ...
    },
    "type": "magento2-language",
    "extra": {
        "map": [
            [
                "*",
                "Magento/de_DE"
            ]
        ]
    }
}
```

Final location is `<magento_root>/app/i18n/Magento/de_DE`

### Magento Library
`"type": "magento2-library"`

Support for libraries located in `lib/internal` instead of in the `vendor` directory.

Example:

```json
{
    "name": "magento/framework",
    "description": "N/A",
    "require": {
       ...
    },
    "type": "magento2-library",
    "extra": {
        "map": [
            [
                "*",
                "Magento/Framework"
            ]
        ]
    }
}
```

Final location is `<magento_root>/lib/internal/Magento/Framework`

### Magento Component
`"type": "magento2-component"`

Installation location: Magento root directory

Example:

```json
{
    "name": "magento/migration-tool",
    "description": "N/A",
    "require": {
        ...
    },
    "type": "magento2-component",
    "extra": {
        "map": [
            [
                "*",
                "dev/tools/Magento/Tools/Migration"
            ]
        ]
    }
}
```

Final location is `<magento_root>/dev/tools/Magento/Tools/Migration`


## Autoload
After handling all Magento components, `<magento_root>/app/etc/vendor_path.php` specifies the path to your `vendor` directory.

This information allows the Magento application to utilize the Composer autoloader for any libraries installed in the `vendor` directory. The path to `vendor` varies between particular installations and depends on the `magento_root` setting for the Magento Composer installer. That's why it should be generated for each installation.

You must run `composer install` to install dependencies for a new application or `composer update` to update dependencies for an existing application.

## Deployment Strategy
The Magneto Composer Installer uses the `copy` deployment strategy. It copies each file or directory from the `vendor` directory to its designated location based on the `extra/map` section in the component's `composer.json`.

There are [other deployment strategies](https://github.com/magento/magento-composer-installer/blob/master/doc/Deploy.md) that could be used; however, we don't guarantee that any of them will work.

# Notes
- The extra->magento-root-dir option is no longer supported. It displays only to preseve backward compatibility.