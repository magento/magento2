# Magento_Authorizenet module

The Magento_Authorizenet module is a part of the staging functionality in Magento Commerce. The module adds the “Configurations” tab and the configuration wizard to the Schedule Update form of a product. You can change the Configurable Product attributes in campaigns. These updates are shown on the campaign dashboard.
## Extensibility

Extension developers can interact with the Magento_Authorizenet module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Authorizenet module.

### Events

This module dispatches the following events:

 - `checkout_directpost_placeOrder` event in the `\Magento\Authorizenet\Controller\Directpost\Payment\Place::placeCheckoutOrder()` method. Parameters:
   - `result` is a data object (`\Magento\Framework\DataObject` class).
   - `action` is a controller object (`\Magento\Authorizenet\Controller\Directpost\Payment\Place`).
 
 - `order_cancel_after` event in the `\Magento\Authorizenet\Model\Directpost::declineOrder()` method. Parameters:
   - `order` is an order object (`\Magento\Sales\Model\Order` class).
   

This module observes the following events:

 - `checkout_submit_all_after` event in the `Magento\Authorizenet\Observer\SaveOrderAfterSubmitObserver` file.
 - `checkout_directpost_placeOrder` event in the `Magento\Authorizenet\Observer\AddFieldsToResponseObserver` file.

For information about events in Magento 2, see [Events and observers](http://devdocs.magento.com/guides/v2.3/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `adminhtml_authorizenet_directpost_payment_redirect`

This module introduces the following layouts and layout handles in the `view/frontend/layout` directory:

- `authorizenet_directpost_payment_backendresponse`
- `authorizenet_directpost_payment_redirect`
- `authorizenet_directpost_payment_response`

For more information about layouts in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.3/frontend-dev-guide/layouts/layout-overview.html).
