# Event

**Event** library provides supports for Magento events.

 * Event manager is responsible for event configuration processing and event dispatching. All client code that dispatches events must ask for \Magento\Framework\Event\Manager in constructor.
 * Event config provides interface to retrieve related observers configuration by specified event name.
 * Event observer object passes data from the code that fires the event to the observer function. There are two special types of observer objects supported in this library: Cron, and Regex. Each one has its own unique functionality in addition to basic observer functionality.
