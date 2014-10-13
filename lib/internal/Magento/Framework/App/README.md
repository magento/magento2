# App

**App** library provides a bootstrap of the Magento application and various components that support Magento application. The bootstrap is called from Magento entry point scripts, such as index.php. It uses this bootstrap to perform initialization routines – such as set preference of error handling, initialize autoloader, set profiling options, set default timezone, etc.

 * A few **entry point applications** that are provided in this library:
  * HTTP - *\Magento\Framework\App\Http* implements the Magento HTTP application.
  * Static Resource - *\Magento\Framework\App\StaticResource* is an application for retrieving static resources (e.g., CSS, JS, images). It allows postponing any actions with a file until it is requested.
  * Cron - \Magento\Framework\App\Cron is an application for running some jobs by schedule.
  
**Request dispatching** is supported in this library. *FrontController* is used as an interface for application. It accepts *RequestInterface* and sends a *ResponseInterface*. It requests routing with implementations of *RouterInterface* that can be configured as arguments for *RouterList*. Control is passed to *Router\DefaultRouter* when request is not matched by any router. *Router\NoRouteHandlerList* can be used to configure custom NoRoute handlers. After request matching router uses *ActionFactory* to create instances of *ActionInterface* responsible for request processing. *ActionInterface* implementations can use *ResponseFactory* to create instances of *ResponseInterface*. *Action\ActionAbstract* provides some basic behavior for application actions. *Action\Forward* is used for forwarding request to different action within same process. *Action\Redirect* is used to do redirects to different url.  Response interfaces provided with this library:

  * \Magento\Framework\App\ResponseInterface - base response interface
  * \Magento\Framework\App\Response\HttpInterface - response interface with methods specific for HTTP response
  * \Magento\Framework\App\Response\FileInterface - HTTP response interface with methods specific for sending files content via HTTP request
  
**Application Deployment Configuration** is supported in this library. It supports basic settings for Magento initializations and cache configuration