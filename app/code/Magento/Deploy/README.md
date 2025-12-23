# Magento_Deploy module

This module holds a collection of services and command line tools to help with application deployment.
To execute this command, run `bin/magento setup:static-content:deploy` from the application root directory.
This module contains two additional commands that allow switching between application modes (for instance from
development to production) and show the current application mode. To change the mode, run `bin/magento deploy:mode:set [mode]`.
Where `mode` can be one of the following:

- development
- production

When switching to production mode, you can pass an optional parameter `skip-compilation` to skip compiling static files, CSS, and run the compilation process.

## Installation

This module is installed automatically (using the native install mechanism) without any additional actions.

## Additional information

You can get more information about deployment in the [Adobe Developer documentation](https://developer.adobe.com/commerce/php/development/deployment/).
