Components of Magento application use caches in their implementation. The **Magento\Cache** library provides an interface for cache storage and segmentation (a.k.a. "types").

**Magento\Framework\App\Cache** extends **Magento\Cache** and provides more specific features:

 * State of cache segments (enabled/disabled) and managing their state
 * Pool of cache frontends
 * List of cache segments (types)
 * Specific cache segments: blocks, collections, configurations, layouts, translations
