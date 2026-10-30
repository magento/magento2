# Magento_PageCache module

This module provides functionality for caching full page content in the Magento 
application. An administrator may switch between built-in caching and Varnish 
caching. Built-in caching is the default and ready to use without requiring any 
external tools.

Requests and responses are managed by the PageCache plugin. It loads data from 
cache and returns a response. If data is not present in cache, it passes the 
request to Magento and waits for the response. The response is then saved in 
cache.

Blocks can be set as private blocks by setting the property `_isScopePrivate` to 
`true`. These blocks contain personalized information and are not cached on the 
server. These blocks are rendered using an AJAX call after the page is loaded. 
Contents are cached in the browser instead.

Blocks can also be set as non-cacheable by setting the `cacheable` attribute in 
layout XML files. For example:

```xml
<block class="Block\Class" name="blockname" cacheable="false" />
```

Pages containing such blocks are not cached.
