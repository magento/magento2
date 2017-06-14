###Magento_NewsletterApi

this module solely defines a service contract and web api for newsletter implementations.

The provided interfaces can be implemented to integrate custom newsletter capabilities into the platform

The service contract for the newsletter has been extracted since it is very common that a newsletter implementation should completely replace the default implementation provided by the platform.

3rd party integrators can use this service contract and completely replace the Magento_Newsletter module, if needed.
