# Overview
## Purpose of module

Deploy is a module that holds collection of services and command line tools to help with Magento application deployment. 
To execute this command, please, run "bin/magento setup:static-content:deploy" from the Magento root directory.
Deploy module contains 2 additional commands that allows switching between application modes (for instance from 
development to
production) and show current application mode. To change the mode run "bin/magento deploy:mode:set [mode]".
Where mode can be one of the following:
 - development
 - production
When switching to production mode, you can pass optional parameter skip-compilation to do not compile static files, CSS 
and do not run the compilation process.

# Deployment
## System requirements

## Install
The Magento_Deploy module is installed automatically (using the native Magento install mechanism) without any additional actions.
