# Magento_Checkout module
Magento\Checkout module allows merchant to register sale transaction with the customer. Module implements consumer flow
that includes such actions like adding products to cart, providing shipping and billing information and confirming
the purchase.

#### Observer
  This module observes the following events
    `etc/events.xml`
      `sales_quote_save_after` event in 
      `Magento\Checkout\Observer\SalesQuoteSaveAfterObserver` file.
    `/etc/frontend/events.xml`
      `customer_login` event in `Magento\Checkout\Observer\LoadCustomerQuoteObserver`
       file.
      `customer_logout` event in `Magento\Checkout\Observer\UnsetAllObserver`
  
  ### Layouts
  The module interacts with the following layout handles in the 
    `view/frontend/layout`
       `catalog_category_view`
       `catalog_product_view`
       `checkout_cart_configure`
       `checkout_cart_configure_type_simple`
       `checkout_cart_index`
       `checkout_cart_item_renderers`
       `checkout_cart_sidebar_item_price_renderers`
       `checkout_cart_sidebar_item_renderers`
       `checkout_cart_sidebar_total_renderers`
       `checkout_index_index`
       `checkout_item_price_renderers`
       `checkout_onepage_failure`
       `checkout_onepage_review_item_renderers`
       `checkout_onepage_success`
       `default`