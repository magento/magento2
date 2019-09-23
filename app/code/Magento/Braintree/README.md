# Magento_Braintree module

The Magento_Braintree module implements integration with the Braintree payment system.

## Extensibility

Extension developers can interact with the Magento_Braintree module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Braintree module.

### Events

This module observes the following events:

 - `payment_method_assign_data_braintree` event in `Magento\Braintree\Observer\DataAssignObserver` file.
 - `payment_method_assign_data_braintree_paypal` event in `Magento\Braintree\Observer\DataAssignObserver` file.
 - `shortcut_buttons_container` event in `Magento\Braintree\Observer\AddPaypalShortcuts` file.

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module interacts with the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `braintree_paypal_review`
- `checkout_index_index`
- `multishipping_checkout_billing`
- `vault_cards_listaction`

This module interacts with the following layout handles in the `view/frontend/layout` directory:

- `adminhtml_system_config_edit`
- `braintree_report_index`
- `sales_order_create_index`
- `sales_order_create_load_block_billing_method`

For more information about layouts in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.3/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend admin notifications using the `view/adminhtml/ui_component/braintree_report.xml` configuration file.

For information about UI components in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.3/ui_comp_guide/bk-ui_comps.html).

## Additional information

For information about significant changes in patch releases, see [2.3.x Release information](https://devdocs.magento.com/guides/v2.3/release-notes/bk-release-notes.html).
