**Magento\Framework\App** implements most of the Magento application framework.

Unlike other components of **Magento\Framework** that are generic libraries not specific to Magento application, the **Magento\Framework\App** is "aware of" Magento application intentionally.

The library implements a variety of features of the Magento application:
 * bootstrap and initialization parameters
 * error handling
 * entry point handlers (application scripts):
  * HTTP -- the web-application entry point for serving pages of Storefront, Admin, etc
  * Static Resource -- for retrieving and serving static content (CSS, JavaScript, images)
  * Cron -- for launching cron jobs
 * Object manager, filesystem components (inheritors specific to Magento application)
 * Caching, cache types
 * Language packages, dictionaries
 * DB connection configuration and pool
 * Request dispatching, routing, front controller
 * Services for view layer
 * Application areas
