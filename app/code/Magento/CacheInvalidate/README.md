# Magento_CacheInvalidate module

The **Magento_CacheInvalidate** module is used to invalidate the Varnish cache if it is configured.

The **Magento_CacheInvalidate** module listens for events that request the cache to be flushed or cause the cache to be invalid, then sends Varnish a purge request using cURL.
