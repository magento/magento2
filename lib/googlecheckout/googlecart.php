<?php
/*
 * Copyright (C) 2007 Google Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *      http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Classes used to build a shopping cart and submit it to Google Checkout
 * @version $Id: googlecart.php 1234 2007-09-25 14:58:57Z ropu $
 */

  define('MAX_DIGITAL_DESC', 1024);
  
 /**
  * Creates a Google Checkout shopping cart and posts it 
  * to the google checkout sandbox or production environment
  * Refer demo/cartdemo.php for different use case scenarios for this code
  */
  class GoogleCart {
    var $merchant_id;
    var $merchant_key;
    var $variant = false;
    var $currency;
    var $server_url;
    var $schema_url;
    var $base_url;
    var $checkout_url;
    var $checkout_diagnose_url;
    var $request_url;
    var $request_diagnose_url;

    var $cart_expiration = "";
    var $merchant_private_data = "";
    var $edit_cart_url = "";
    var $continue_shopping_url = "";
    var $request_buyer_phone = "";
    var $merchant_calculated_tax = "";
    var $merchant_calculations_url = "";
    var $accept_merchant_coupons = "";
    var $accept_gift_certificates = "";
    var $rounding_mode;
    var $rounding_rule;
    var $analytics_data;

    var $item_arr;
    var $shipping_arr;
    var $default_tax_rules_arr;
    var $alternate_tax_tables_arr;
    var $xml_data;
    
    var $googleAnalytics_id = false;
    var $thirdPartyTackingUrl = false;
    var $thirdPartyTackingParams = array();
    
		// For HTML API Conversion
		
    // This tags are those that can be used more than once as a sub tag
    // so a "-#" must be added always
    /**
     * used when using the html api
     * tags that can be used more than once, so they need to be numbered
     * ("-#" suffix)
     */
    var $multiple_tags = array(
                          'flat-rate-shipping' => array(), 
                          'merchant-calculated-shipping' => array(), 
                          'pickup' => array(), 
                          'parameterized-url' => array(), 
                          'url-parameter' => array(), 
                          'item' => array(), 
                          'us-state-area' => array('tax-area'), 
                          'us-zip-area' => array('tax-area'), 
                          'us-country-area' => array('tax-area'), 
                          'postal-area' => array('tax-area'), 
                          'alternate-tax-table' => array(), 
                          'world-area' => array('tax-area'),                      
                          'default-tax-rule' => array(), 
                          'alternate-tax-rule' => array(), 
                          'gift-certificate-adjustment' => array(), 
                          'coupon-adjustment' => array(), 
                          'coupon-result' => array(), 
                          'gift-certificate-result' => array(), 
                          'method' => array(), 
                          'anonymous-address' => array(), 
                          'result' => array(), 
                          'string' => array(), 
                          );
    
    var $ignore_tags = array(
                        'xmlns' => true,
                        'checkout-shopping-cart' => true,
                        // Dont know how to translate these tag yet
                        'merchant-private-data' => true,
                        'merchant-private-item-data' => true,
    );



		/**
		 * Has all the logic to build the cart's xml (or html) request to be 
		 * posted to google's servers.
		 * 
		 * @param string $id the merchant id
		 * @param string $key the merchant key
		 * @param string $server_type the server type of the server to be used, one 
		 *                            of 'sandbox' or 'production'.
		 *                            defaults to 'sandbox'
		 * @param string $currency the currency of the items to be added to the cart
     *                         , as of now values can be 'USD' or 'GBP'.
     *                         defaults to 'USD'
		 */
    function GoogleCart($id, $key, $server_type="sandbox", $currency="USD") {
      $this->merchant_id = $id;
      $this->merchant_key = $key;
      $this->currency = $currency;

      if(strtolower($server_type) == "sandbox") {
        $this->server_url = "https://sandbox.google.com/checkout/";
      } else {
        $this->server_url=  "https://checkout.google.com/";  
      }


      $this->schema_url = "http://checkout.google.com/schema/2";
      $this->base_url = $this->server_url . "api/checkout/v2/"; 
      $this->checkout_url = $this->base_url . "checkout/Merchant/" . $this->merchant_id;
      $this->checkoutForm_url = $this->base_url . "checkoutForm/Merchant/" . $this->merchant_id;

      //The item, shipping and tax table arrays are initialized
      $this->item_arr = array();
      $this->shipping_arr = array(); 
      $this->alternate_tax_tables_arr = array();
    }

    /**
     * Sets the cart's expiration date
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_good-until-date <good-until-date>}
     * 
     * @param string $cart_expire a string representing a date in the 
     *         iso 8601 date and time format: {@link http://www.w3.org/TR/NOTE-datetime}
     * 
     * @return void
     */
    function SetCartExpiration($cart_expire) {
      $this->cart_expiration = $cart_expire;
    }

    /**
     * Sets the merchant's private data.
     * 
     * Google Checkout will return this data in the
     * <merchant-calculation-callback> and the 
     * <new-order-notification> for the order.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_merchant-private-data <merchant-private-data>}
     * 
     * @param MerchantPrivateData $data an object which contains the data to be 
     *                                  sent as merchant-private-data
     * 
     * @return void
     */
    function SetMerchantPrivateData($data) {
      $this->merchant_private_data = $data;
    }

    /**
     * Sets the url where the customer can edit his cart.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_edit-cart-url <edit-cart-url>}
     * 
     * @param string $url the merchant's site edit cart url
     * @return void
     */
    function SetEditCartUrl($url) {
      $this->edit_cart_url= $url;
    }

    /**
     * Sets the continue shopping url, which allows the customer to return 
     * to the merchant's site after confirming an order.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_continue-shopping-url <continue-shopping-url>}
     * 
     * @param string $url the merchant's site continue shopping url
     * @return void
     */
    function SetContinueShoppingUrl($url) {
      $this->continue_shopping_url = $url;
    }

    /**
     * Sets whether the customer must enter a phone number to complete an order.
     * If set to true, the customer must enter a number, which Google Checkout
     * will return in the new order notification for the order.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_request-buyer-phone-number <request-buyer-phone-number>}
     * 
     * @param bool $req true if the customer's phone number is *required*
     *                  to complete an order.
     *                  defaults to false.
     * @return void
     */
    function SetRequestBuyerPhone($req) {
      $this->request_buyer_phone = $this->_GetBooleanValue($req, "false");
    }

    /**
     * Sets the information about calculations that will be performed by the 
     * merchant.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_merchant-calculations <merchant-calculations>}
     * 
     * @param string $url the merchant calculations callback url
     * @param bool $tax_option true if the merchant has to do tax calculations.
     *                         defaults to false.
     * @param bool $coupons true if the merchant accepts discount coupons.
     *                         defaults to false.
     * @param bool $gift_cert true if the merchant accepts gift certificates.
     *                         defaults to false.
     * @return void
     */
    function SetMerchantCalculations($url, $tax_option = "false",
        $coupons = "false", $gift_cert = "false") {
      $this->merchant_calculations_url = $url;
      $this->merchant_calculated_tax = $this->_GetBooleanValue($tax_option, "false");
      $this->accept_merchant_coupons = $this->_GetBooleanValue($coupons, "false");
      $this->accept_gift_certificates = $this->_GetBooleanValue($gift_cert, "false");
    }

    /**
     * Add an item to the cart.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_item <item>}
     * 
     * @param GoogleItem $google_item an object that represents an item 
     *                                (defined in googleitem.php)
     * 
     * @return void
     */
    function AddItem($google_item) {
      $this->item_arr[] = $google_item;
    }

    /**
     * Add a shipping method to the cart.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_shipping-methods <shipping-methods>}
     * 
     * @param object $ship an object that represents a shipping method, must be 
     *                     one of the methods defined in googleshipping.php
     * 
     * @return void
     */
    function AddShipping($ship) {
      $this->shipping_arr[] = $ship;
    }

    /**
     * Add a default tax rule to the cart.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_default-tax-rule <default-tax-rule>}
     * 
     * @param GoogleDefaultTaxRule $rules an object that represents a default
     *                                    tax rule (defined in googletax.php)
     * 
     * @return void
     */
    function AddDefaultTaxRules($rules) {
      $this->default_tax_table = true;
      $this->default_tax_rules_arr[] = $rules;
    }

    /**
     * Add an alternate tax table to the cart.
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_alternate-tax-table <alternate-tax-table>}
     * 
     * @param GoogleAlternateTaxTable $tax an object that represents an 
     *                                     alternate tax table 
     *                                     (defined in googletax.php)
     * 
     * @return void
     */
    function AddAlternateTaxTables($tax) {
      $this->alternate_tax_tables_arr[] = $tax;
    }

    /**
     * Set the policy to be used to round monetary values.
     * Rounding policy explanation here:
     * {@link http://code.google.com/apis/checkout/developer/Google_Checkout_Rounding_Policy.html}
     * 
     * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_rounding-policy <rounding-policy>}
     * 
     * @param string $mode one of "UP", "DOWN", "CEILING", "HALF_DOWN" 
     *                     or "HALF_EVEN", described here: {@link http://java.sun.com/j2se/1.5.0/docs/api/java/math/RoundingMode.html}
     * @param string $rule one of "PER_LINE", "TOTAL"
     * 
     * @return void
     */
    function AddRoundingPolicy($mode, $rule) {
      switch ($mode) {
        case "UP":
        case "DOWN":
        case "CEILING":
        case "HALF_UP":
        case "HALF_DOWN":
        case "HALF_EVEN":
            $this->rounding_mode = $mode;
            break;
        default:
            break;
      }
      switch ($rule) {
        case "PER_LINE":
        case "TOTAL":
            $this->rounding_rule = $rule;
            break;
        default:
            break;
      }
    }
    
    /**
     * Set the google analytics data.
     * 
     * {@link http://code.google.com/apis/checkout/developer/checkout_analytics_integration.html info on Checkout and Analytics integration}
     * 
     * @param string $data the analytics data
     * 
     * @return void
     */
    function SetAnalyticsData($data) {
      $this->analytics_data = $data;
    }
    
    /**
     * Add a google analytics tracking id.
     * 
     * {@link http://code.google.com/apis/checkout/developer/checkout_analytics_integration.html info on Checkout and Analytics integration}
     * 
     * @param string $GA_id the google analytics id
     * 
     * @return void
     */
    function AddGoogleAnalyticsTracking($GA_id) {
    	$this->googleAnalytics_id = $GA_id;
    }
    
    /**
     * Add third-party tracking to the cart
     * 
     * Described here:
     * {@link http://code.google.com/apis/checkout/developer/checkout_analytics_integration.html#googleCheckoutAnalyticsIntegrationAlternate}
     * 
     * @param $tracking_attr_types attributes to be tracked, one of 
     *                            ('buyer-id',
     *                             'order-id',
     *                             'order-subtotal',
     *                             'order-subtotal-plus-tax',
     *                             'order-subtotal-plus-shipping',
     *                             'order-total',
     *                             'tax-amount',
     *                             'shipping-amount',
     *                             'coupon-amount',
     *                             'coupon-amount',
     *                             'billing-city',
     *                             'billing-region',
     *                             'billing-postal-code',
     *                             'billing-country-code',
     *                             'shipping-city',
     *                             'shipping-region',
     *                             'shipping-postal-code',
     *                             'shipping-country-code')
     * More info http://code.google.com/apis/checkout/developer/checkout_pixel_tracking.html#googleCheckout_tag_url-parameter
     */
    function AddThirdPartyTracking($url, $tracking_param_types = array()) {
      $this->thirdPartyTackingUrl = $url;
      $this->thirdPartyTackingParams = $tracking_param_types;
    }

    /**
     * Builds the cart's xml to be sent to Google Checkout.
     * 
     * @return string the cart's xml
     */
    function GetXML() {
      require_once('xml-processing/gc_xmlbuilder.php');

      $xml_data = new gc_XmlBuilder();

      $xml_data->Push('checkout-shopping-cart',
          array('xmlns' => $this->schema_url));
      $xml_data->Push('shopping-cart');

      //Add cart expiration if set
      if($this->cart_expiration != "") {
        $xml_data->Push('cart-expiration');
        $xml_data->Element('good-until-date', $this->cart_expiration);
        $xml_data->Pop('cart-expiration');
      }

      //Add XML data for each of the items
      $xml_data->Push('items');
      foreach($this->item_arr as $item) {
        $xml_data->Push('item');
        $xml_data->Element('item-name', $item->item_name);
        $xml_data->Element('item-description', $item->item_description);
        $xml_data->Element('unit-price', $item->unit_price,
            array('currency' => $this->currency));
        $xml_data->Element('quantity', $item->quantity);
        if($item->merchant_private_item_data != '') {
//          echo get_class($item->merchant_private_item_data);
          if(is_a($item->merchant_private_item_data, 
                                              'merchantprivate')) {
            $item->merchant_private_item_data->AddMerchantPrivateToXML($xml_data);
          }
          else {
            $xml_data->Element('merchant-private-item-data', 
                                             $item->merchant_private_item_data);
          }
        }
        if($item->merchant_item_id != '')
          $xml_data->Element('merchant-item-id', $item->merchant_item_id);
        if($item->tax_table_selector != '')
          $xml_data->Element('tax-table-selector', $item->tax_table_selector);
//      Carrier calculation
        if($item->item_weight != '' && $item->numeric_weight !== '') {
          $xml_data->EmptyElement('item-weight', array( 'unit' => $item->item_weight,
                                                'value' => $item->numeric_weight
                                               ));
        }
//      New Digital Delivery Tags
        if($item->digital_content) {
          $xml_data->push('digital-content');
          if(!empty($item->digital_url)) {
            $xml_data->element('description', substr($item->digital_description,
                                                          0, MAX_DIGITAL_DESC));
            $xml_data->element('url', $item->digital_url);
//            To avoid NULL key message in GC confirmation Page
            if(!empty($item->digital_key)) {
              $xml_data->element('key', $item->digital_key);
            }
          }
          else {
            $xml_data->element('email-delivery', 
                      $this->_GetBooleanValue($item->email_delivery, "true"));
          }
          $xml_data->pop('digital-content');          
        }
        $xml_data->Pop('item');
      }
      $xml_data->Pop('items');

      if($this->merchant_private_data != '') {
        if(is_a($this->merchant_private_data, 'merchantprivate')) {
          $this->merchant_private_data->AddMerchantPrivateToXML($xml_data);
        }
        else {
          $xml_data->Element('merchant-private-data',
                                                  $this->merchant_private_data);
        }
      }
      $xml_data->Pop('shopping-cart');

      $xml_data->Push('checkout-flow-support');
      $xml_data->Push('merchant-checkout-flow-support');
      if($this->edit_cart_url != '')
        $xml_data->Element('edit-cart-url', $this->edit_cart_url);
      if($this->continue_shopping_url != '')
        $xml_data->Element('continue-shopping-url',
            $this->continue_shopping_url);

      if(count($this->shipping_arr) > 0)
        $xml_data->Push('shipping-methods');

      //Add the shipping methods
      foreach($this->shipping_arr as $ship) {
        //Pickup shipping handled in else part
        if($ship->type == "flat-rate-shipping" ||
           $ship->type == "merchant-calculated-shipping"
//  If shipping-company calc support addr-filtering and shipping restrictions as a subatag of shipping-company-calculated-shipping
//           ||$ship->type == "shipping-company-calculated-shipping" 
           ) {
          $xml_data->Push($ship->type, array('name' => $ship->name));
          $xml_data->Element('price', $ship->price,
              array('currency' => $this->currency));

          $shipping_restrictions = $ship->shipping_restrictions;
          if (isset($shipping_restrictions)) {
            $xml_data->Push('shipping-restrictions');

            if ($shipping_restrictions->allow_us_po_box === true) {
              $xml_data->Element('allow-us-po-box', "true");
            } else {
              $xml_data->Element('allow-us-po-box', "false");
            }

            //Check if allowed restrictions specified
            if($shipping_restrictions->allowed_restrictions) {
              $xml_data->Push('allowed-areas');
              if($shipping_restrictions->allowed_country_area != "")
                $xml_data->EmptyElement('us-country-area',
                    array('country-area' =>
                    $shipping_restrictions->allowed_country_area));
              foreach($shipping_restrictions->allowed_state_areas_arr as $current) {
                $xml_data->Push('us-state-area');
                $xml_data->Element('state', $current);
                $xml_data->Pop('us-state-area');
              }
              foreach($shipping_restrictions->allowed_zip_patterns_arr as $current) {
                $xml_data->Push('us-zip-area');
                $xml_data->Element('zip-pattern', $current);
                $xml_data->Pop('us-zip-area');
              }
              if($shipping_restrictions->allowed_world_area === true) {
                $xml_data->EmptyElement('world-area');
              }
              for($i=0; $i<count($shipping_restrictions->allowed_country_codes_arr); $i++) {
                $xml_data->Push('postal-area');
                $country_code = $shipping_restrictions->allowed_country_codes_arr[$i];
                $postal_pattern = $shipping_restrictions->allowed_postal_patterns_arr[$i];
                $xml_data->Element('country-code', $country_code);
                if ($postal_pattern != "") {
                  $xml_data->Element('postal-code-pattern', $postal_pattern);
                }
                $xml_data->Pop('postal-area');
              }
              $xml_data->Pop('allowed-areas');
            }

            if($shipping_restrictions->excluded_restrictions) { 
              if (!$shipping_restrictions->allowed_restrictions) {
                $xml_data->EmptyElement('allowed-areas');
              }
              $xml_data->Push('excluded-areas');
              if($shipping_restrictions->excluded_country_area != "")
                $xml_data->EmptyElement('us-country-area',
                    array('country-area' => 
                    $shipping_restrictions->excluded_country_area));
              foreach($shipping_restrictions->excluded_state_areas_arr as $current) {
                $xml_data->Push('us-state-area');
                $xml_data->Element('state', $current);
                $xml_data->Pop('us-state-area');
              }
              foreach($shipping_restrictions->excluded_zip_patterns_arr as $current) {
                $xml_data->Push('us-zip-area');
                $xml_data->Element('zip-pattern', $current);
                $xml_data->Pop('us-zip-area');
              }
              for($i=0; $i<count($shipping_restrictions->excluded_country_codes_arr); $i++) {
                $xml_data->Push('postal-area');
                $country_code = $shipping_restrictions->excluded_country_codes_arr[$i];
                $postal_pattern = $shipping_restrictions->excluded_postal_patterns_arr[$i];
                $xml_data->Element('country-code', $country_code);
                if ($postal_pattern != "") {
                  $xml_data->Element('postal-code-pattern', $postal_pattern);
                }
                $xml_data->Pop('postal-area');
              }
              $xml_data->Pop('excluded-areas');
            }
            $xml_data->Pop('shipping-restrictions');
          }

          if ($ship->type == "merchant-calculated-shipping") {
            $address_filters = $ship->address_filters;
            if (isset($address_filters)) {
              $xml_data->Push('address-filters');

              if ($address_filters->allow_us_po_box === true) {
                $xml_data->Element('allow-us-po-box', "true");
              } else {
                $xml_data->Element('allow-us-po-box', "false");
              }

              //Check if allowed restrictions specified
              if($address_filters->allowed_restrictions) {
                $xml_data->Push('allowed-areas');
                if($address_filters->allowed_country_area != "")
                  $xml_data->EmptyElement('us-country-area',
                      array('country-area' =>
                      $address_filters->allowed_country_area));
                foreach($address_filters->allowed_state_areas_arr as $current) {
                  $xml_data->Push('us-state-area');
                  $xml_data->Element('state', $current);
                  $xml_data->Pop('us-state-area');
                }
                foreach($address_filters->allowed_zip_patterns_arr as $current) {
                  $xml_data->Push('us-zip-area');
                  $xml_data->Element('zip-pattern', $current);
                  $xml_data->Pop('us-zip-area');
                }
                if($address_filters->allowed_world_area === true) {
                  $xml_data->EmptyElement('world-area');
                }
                for($i=0; $i<count($address_filters->allowed_country_codes_arr); $i++) {
                  $xml_data->Push('postal-area');
                  $country_code = $address_filters->allowed_country_codes_arr[$i];
                  $postal_pattern = $address_filters->allowed_postal_patterns_arr[$i];
                  $xml_data->Element('country-code', $country_code);
                  if ($postal_pattern != "") {
                    $xml_data->Element('postal-code-pattern', $postal_pattern);
                  }
                  $xml_data->Pop('postal-area');
                }
                $xml_data->Pop('allowed-areas');
              }

              if($address_filters->excluded_restrictions) { 
                if (!$address_filters->allowed_restrictions) {
                  $xml_data->EmptyElement('allowed-areas');
                }
                $xml_data->Push('excluded-areas');
                if($address_filters->excluded_country_area != "")
                  $xml_data->EmptyElement('us-country-area',
                      array('country-area' => 
                      $address_filters->excluded_country_area));
                foreach($address_filters->excluded_state_areas_arr as $current) {
                  $xml_data->Push('us-state-area');
                  $xml_data->Element('state', $current);
                  $xml_data->Pop('us-state-area');
                }
                foreach($address_filters->excluded_zip_patterns_arr as $current) {
                  $xml_data->Push('us-zip-area');
                  $xml_data->Element('zip-pattern', $current);
                  $xml_data->Pop('us-zip-area');
                }
                for($i=0; $i<count($address_filters->excluded_country_codes_arr); $i++) {
                  $xml_data->Push('postal-area');
                  $country_code = $address_filters->excluded_country_codes_arr[$i];
                  $postal_pattern = $address_filters->excluded_postal_patterns_arr[$i];
                  $xml_data->Element('country-code', $country_code);
                  if ($postal_pattern != "") {
                    $xml_data->Element('postal-code-pattern', $postal_pattern);
                  }
                  $xml_data->Pop('postal-area');
                }
                $xml_data->Pop('excluded-areas');
              }
              $xml_data->Pop('address-filters');
            }
          }
          $xml_data->Pop($ship->type);
        }
        else if ($ship->type == "carrier-calculated-shipping"){
//          $xml_data->Push($ship->type, array('name' => $ship->name));
          $xml_data->Push($ship->type);
          $xml_data->Push('carrier-calculated-shipping-options');
          $CCSoptions = $ship->CarrierCalculatedShippingOptions;
          foreach($CCSoptions as $CCSoption){
            $xml_data->Push('carrier-calculated-shipping-option');
            $xml_data->Element('price', $CCSoption->price, 
                array('currency' => $this->currency));
            $xml_data->Element('shipping-company', $CCSoption->shipping_company);
            $xml_data->Element('shipping-type', $CCSoption->shipping_type);
            $xml_data->Element('carrier-pickup', $CCSoption->carrier_pickup);
            if(!empty($CCSoption->additional_fixed_charge)) {
              $xml_data->Element('additional-fixed-charge',
                  $CCSoption->additional_fixed_charge, 
                  array('currency' => $this->currency));
            }
            if(!empty($CCSoption->additional_variable_charge_percent)) {
              $xml_data->Element('additional-variable-charge-percent',
                  $CCSoption->additional_variable_charge_percent);
            }
            $xml_data->Pop('carrier-calculated-shipping-option');
          }
          $xml_data->Pop('carrier-calculated-shipping-options');
//          $ShippingPackage = $ship->ShippingPackage;
          $xml_data->Push('shipping-packages');
          $xml_data->Push('shipping-package');
          $xml_data->Push('ship-from', array('id' => $ship->ShippingPackage->ship_from->id));
          $xml_data->Element('city', $ship->ShippingPackage->ship_from->city);
          $xml_data->Element('region', $ship->ShippingPackage->ship_from->region);
          $xml_data->Element('postal-code', $ship->ShippingPackage->ship_from->postal_code);
          $xml_data->Element('country-code', $ship->ShippingPackage->ship_from->country_code);
          $xml_data->Pop('ship-from');

          $xml_data->EmptyElement('width', array('unit' => $ship->ShippingPackage->unit,
                                         'value' => $ship->ShippingPackage->width
                                          ));
          $xml_data->EmptyElement('length', array('unit' => $ship->ShippingPackage->unit,
                                          'value' => $ship->ShippingPackage->length
                                          ));
          $xml_data->EmptyElement('height', array('unit' => $ship->ShippingPackage->unit,
                                          'value' => $ship->ShippingPackage->height
                                          ));
          $xml_data->Element('delivery-address-category',
                $ship->ShippingPackage->delivery_address_category);
          $xml_data->Pop('shipping-package');
          $xml_data->Pop('shipping-packages');

          $xml_data->Pop($ship->type);          
        }
        else if ($ship->type == "pickup") {
          $xml_data->Push('pickup', array('name' => $ship->name));
          $xml_data->Element('price', $ship->price, 
              array('currency' => $this->currency));
          $xml_data->Pop('pickup');
        }
      }
      if(count($this->shipping_arr) > 0)
        $xml_data->Pop('shipping-methods');

      if($this->request_buyer_phone != "")
        $xml_data->Element('request-buyer-phone-number', 
            $this->request_buyer_phone);

      if($this->merchant_calculations_url != "") {
        $xml_data->Push('merchant-calculations');
        $xml_data->Element('merchant-calculations-url', 
            $this->merchant_calculations_url);
        if($this->accept_merchant_coupons != "") {
          $xml_data->Element('accept-merchant-coupons', 
              $this->accept_merchant_coupons);
        }
        if($this->accept_gift_certificates != "") {
          $xml_data->Element('accept-gift-certificates', 
              $this->accept_gift_certificates);
        }
        $xml_data->Pop('merchant-calculations');
      }
      //Set Third party Tracking
      if($this->thirdPartyTackingUrl) {
        $xml_data->push('parameterized-urls');
        $xml_data->push('parameterized-url', 
           array('url' => $this->thirdPartyTackingUrl));
        if(is_array($this->thirdPartyTackingParams) 
            && count($this->thirdPartyTackingParams)>0) {
          $xml_data->push('parameters');
          foreach($this->thirdPartyTackingParams as $tracking_param_name => 
                                                          $tracking_param_type) {
            $xml_data->emptyElement('url-parameter',
              array('name' => $tracking_param_name,
                    'type' => $tracking_param_type));
          }
          $xml_data->pop('parameters');
        }
        $xml_data->pop('parameterized-url');
        $xml_data->pop('parameterized-urls');
      }

      //Set Default and Alternate tax tables
      if( (count($this->alternate_tax_tables_arr) != 0) 
            || (count($this->default_tax_rules_arr) != 0)) {
        if($this->merchant_calculated_tax != "") {
          $xml_data->Push('tax-tables', 
            array('merchant-calculated' => $this->merchant_calculated_tax));
        }
        else {
          $xml_data->Push('tax-tables');
        }
        if(count($this->default_tax_rules_arr) != 0) {
          $xml_data->Push('default-tax-table');
          $xml_data->Push('tax-rules');
          foreach($this->default_tax_rules_arr as $curr_rule) {

            if($curr_rule->country_area != "") {
              $xml_data->Push('default-tax-rule');
              $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
              $xml_data->Element('rate', $curr_rule->tax_rate);
              $xml_data->Push('tax-area');
              $xml_data->EmptyElement('us-country-area', 
                array('country-area' => $curr_rule->country_area));
              $xml_data->Pop('tax-area');
              $xml_data->Pop('default-tax-rule');
            }

            foreach($curr_rule->state_areas_arr as $current) {
              $xml_data->Push('default-tax-rule');
              $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
              $xml_data->Element('rate', $curr_rule->tax_rate);
              $xml_data->Push('tax-area');
              $xml_data->Push('us-state-area');
              $xml_data->Element('state', $current);
              $xml_data->Pop('us-state-area');
              $xml_data->Pop('tax-area');
              $xml_data->Pop('default-tax-rule');
            }

            foreach($curr_rule->zip_patterns_arr as $current) {
              $xml_data->Push('default-tax-rule');
              $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
              $xml_data->Element('rate', $curr_rule->tax_rate);
              $xml_data->Push('tax-area');
              $xml_data->Push('us-zip-area');
              $xml_data->Element('zip-pattern', $current);
              $xml_data->Pop('us-zip-area');
              $xml_data->Pop('tax-area');
              $xml_data->Pop('default-tax-rule');
            }

            for($i=0; $i<count($curr_rule->country_codes_arr); $i++) {
              $xml_data->Push('default-tax-rule');
              $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
              $xml_data->Element('rate', $curr_rule->tax_rate);
              $xml_data->Push('tax-area');
              $xml_data->Push('postal-area');
              $country_code = $curr_rule->country_codes_arr[$i];
              $postal_pattern = $curr_rule->postal_patterns_arr[$i];
              $xml_data->Element('country-code', $country_code);
              if ($postal_pattern != "") {
                $xml_data->Element('postal-code-pattern', $postal_pattern);
              }
              $xml_data->Pop('postal-area');
              $xml_data->Pop('tax-area');
              $xml_data->Pop('default-tax-rule');
            }

            if ($curr_rule->world_area === true) {
              $xml_data->Push('default-tax-rule');
              $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
              $xml_data->Element('rate', $curr_rule->tax_rate);
              $xml_data->Push('tax-area');
              $xml_data->EmptyElement('world-area');
              $xml_data->Pop('tax-area');
              $xml_data->Pop('default-tax-rule');
            }
          }
          $xml_data->Pop('tax-rules');
          $xml_data->Pop('default-tax-table');
        }

        if(count($this->alternate_tax_tables_arr) != 0) {
          $xml_data->Push('alternate-tax-tables');
          foreach($this->alternate_tax_tables_arr as $curr_table) {
            $xml_data->Push('alternate-tax-table', 
              array('standalone' => $curr_table->standalone,
                    'name' => $curr_table->name));
            $xml_data->Push('alternate-tax-rules');

            foreach($curr_table->tax_rules_arr as $curr_rule) {
              if($curr_rule->country_area != "") {
                $xml_data->Push('alternate-tax-rule');
                $xml_data->Element('rate', $curr_rule->tax_rate);
                $xml_data->Push('tax-area');
                $xml_data->EmptyElement('us-country-area', 
                  array('country-area' => $curr_rule->country_area));
                $xml_data->Pop('tax-area');
                $xml_data->Pop('alternate-tax-rule');
              }

              foreach($curr_rule->state_areas_arr as $current) {
                $xml_data->Push('alternate-tax-rule');
                $xml_data->Element('rate', $curr_rule->tax_rate);
                $xml_data->Push('tax-area');
                $xml_data->Push('us-state-area');
                $xml_data->Element('state', $current);
                $xml_data->Pop('us-state-area');
                $xml_data->Pop('tax-area');
                $xml_data->Pop('alternate-tax-rule');
              }

              foreach($curr_rule->zip_patterns_arr as $current) {
                $xml_data->Push('alternate-tax-rule');
                $xml_data->Element('rate', $curr_rule->tax_rate);
                $xml_data->Push('tax-area');
                $xml_data->Push('us-zip-area');
                $xml_data->Element('zip-pattern', $current);
                $xml_data->Pop('us-zip-area');
                $xml_data->Pop('tax-area');
                $xml_data->Pop('alternate-tax-rule');
              }

              for($i=0; $i<count($curr_rule->country_codes_arr); $i++) {
                $xml_data->Push('alternate-tax-rule');
                $xml_data->Element('rate', $curr_rule->tax_rate);
                $xml_data->Push('tax-area');
                $xml_data->Push('postal-area');
                $country_code = $curr_rule->country_codes_arr[$i];
                $postal_pattern = $curr_rule->postal_patterns_arr[$i];
                $xml_data->Element('country-code', $country_code);
                if ($postal_pattern != "") {
                  $xml_data->Element('postal-code-pattern', $postal_pattern);
                }
                $xml_data->Pop('postal-area');
                $xml_data->Pop('tax-area');
                $xml_data->Pop('alternate-tax-rule');
              }

              if ($curr_rule->world_area === true) {
                $xml_data->Push('alternate-tax-rule');
                $xml_data->Element('rate', $curr_rule->tax_rate);
                $xml_data->Push('tax-area');
                $xml_data->EmptyElement('world-area');
                $xml_data->Pop('tax-area');
                $xml_data->Pop('alternate-tax-rule');
              }
            }
            $xml_data->Pop('alternate-tax-rules');
            $xml_data->Pop('alternate-tax-table');
          }
          $xml_data->Pop('alternate-tax-tables');
        }
        $xml_data->Pop('tax-tables');
      }

      if (($this->rounding_mode != "") && ($this->rounding_rule != "")) {
        $xml_data->Push('rounding-policy');
        $xml_data->Element('mode', $this->rounding_mode);
        $xml_data->Element('rule', $this->rounding_rule);
        $xml_data->Pop('rounding-policy');
      }
      if($this->analytics_data != ''){ 
        $xml_data->Element('analytics-data', $this->analytics_data);
      }

      $xml_data->Pop('merchant-checkout-flow-support');
      $xml_data->Pop('checkout-flow-support');
      $xml_data->Pop('checkout-shopping-cart');

      return $xml_data->GetXML();  
    }
    
    /**
     * Set the Google Checkout button's variant.
     * {@link http://code.google.com/apis/checkout/developer/index.html#google_checkout_buttons}
     * 
     * @param bool $variant true for an enabled button, false for a 
     *                      disabled one
     * 
     * @return void
     */
    function SetButtonVariant($variant) {
      switch ($variant) {
        case false:
            $this->variant = "disabled";
            break;
        case true:
        default:
            $this->variant = "text";
            break;
      }
    }
    
    /**
     * Submit a server-to-server request.
     * Creates a GoogleRequest object (defined in googlerequest.php) and sends 
     * it to the Google Checkout server.
     * 
     * more info:
     * {@link http://code.google.com/apis/checkout/developer/index.html#alternate_technique}
     * 
     * @return array with the returned http status code (200 if OK) in index 0 
     *               and the redirect url returned by the server in index 1
     */
    function CheckoutServer2Server($proxy=array(), $certPath='') {
      #ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
      require_once('googlerequest.php');
      $GRequest = new GoogleRequest($this->merchant_id, 
                      $this->merchant_key, 
                      $this->server_url=="https://checkout.google.com/"?
                                                         "Production":"sandbox",
                      $this->currency);
      $GRequest->SetProxy($proxy);
      $GRequest->SetCertificatePath($certPath);
                      
      return $GRequest->SendServer2ServerCart($this->GetXML());
    }

    /**
     * Get the Google Checkout button's html to be used in a server-to-server
     * request.
     * 
     * {@link http://code.google.com/apis/checkout/developer/index.html#google_checkout_buttons}
     * 
     * @param string $url the merchant's site url where the form will be posted 
     *                    to
     * @param string $size the size of the button, one of 'large', 'medium' or
     *                     'small'.
     *                     defaults to 'large'
     * @param bool $variant true for an enabled button, false for a 
     *                      disabled one. defaults to true. will be ignored if
     *                      SetButtonVariant() was used before.
     * @param string $loc the locale of the button's text, the only valid value
     *                    is 'en_US' (used as default)
     * @param bool $showtext whether to show Google Checkout text or not, 
     *                       defaults to true.
     * @param string $style the background style of the button, one of 'white'
     *                      or 'trans'. defaults to "trans"
     * 
     * @return string the button's html
     */
    function CheckoutServer2ServerButton($url, $size="large", $variant=true,
                                  $loc="en_US",$showtext=true, $style="trans") {

      switch (strtolower($size)) {
        case "medium":
          $width = "168";
          $height = "44";
          break;

        case "small":
          $width = "160";
          $height = "43";
          break;
        case "large":
        default:
          $width = "180";
          $height = "46";
          break;
      }

      if($this->variant == false) {
        switch ($variant) {
          case false:
              $this->variant = "disabled";
              break;
          case true:
          default:
              $this->variant = "text";
              break;
        }
      }
      $data = "<div style=\"width: ".$width."px\">";
      if ($this->variant == "text") {
        $data .= "<div align=center><form method=\"POST\" action=\"". 
                $url . "\"" . ($this->googleAnalytics_id?
                " onsubmit=\"setUrchinInputCode();\"":"") . ">
                <input type=\"image\" name=\"Checkout\" alt=\"Checkout\" 
                src=\"". $this->server_url."buttons/checkout.gif?merchant_id=" .
                $this->merchant_id."&w=".$width. "&h=".$height."&style=".
                $style."&variant=".$this->variant."&loc=".$loc."\" 
                height=\"".$height."\" width=\"".$width. "\" />";
                
        if($this->googleAnalytics_id) {
          $data .= "<input type=\"hidden\" name=\"analyticsdata\" value=\"\">";
        }                
        $data .= "</form></div>";
        if($this->googleAnalytics_id) {                
            $data .= "<!-- Start Google analytics -->
            <script src=\"https://ssl.google-analytics.com/urchin.js\" type=\"".
                "text/javascript\">
            </script>
            <script type=\"text/javascript\">
            _uacct = \"" . $this->googleAnalytics_id . "\";
            urchinTracker();
            </script>
            <script src=\"https://checkout.google.com/files/digital/urchin_po" .
                "st.js\" type=\"text/javascript\"></script>  
            <!-- End Google analytics -->";
        }      } else {
        $data .= "<div><img alt=\"Checkout\" src=\"" .
                "". $this->server_url."buttons/checkout.gif?merchant_id=" .
                "".$this->merchant_id."&w=".$width. "&h=".$height."&style=".$style.
                "&variant=".$this->variant."&loc=".$loc."\" height=\"".$height."\"".
                " width=\"".$width. "\" /></div>";
        
      }
      $data .= "</div>";
      return $data;
    }

    /**
     * Get the Google Checkout button's html.
     * 
     * {@link http://code.google.com/apis/checkout/developer/index.html#google_checkout_buttons}
     * 
     * @param string $size the size of the button, one of 'large', 'medium' or
     *                     'small'.
     *                     defaults to 'large'
     * @param bool $variant true for an enabled button, false for a 
     *                      disabled one. defaults to true. will be ignored if
     *                      SetButtonVariant() was used before.
     * @param string $loc the locale of the button's text, the only valid value
     *                    is 'en_US' (used as default)
     * @param bool $showtext whether to show Google Checkout text or not, 
     *                       defaults to true.
     * @param string $style the background style of the button, one of 'white'
     *                      or 'trans'. defaults to "trans"
     * 
     * @return string the button's html
     */
    function CheckoutButtonCode($size="large", $variant=true, $loc="en_US",
                                               $showtext=true, $style="trans") {

      switch (strtolower($size)) {
        case "medium":
          $width = "168";
          $height = "44";
          break;

        case "small":
          $width = "160";
          $height = "43";
          break;
        case "large":
        default:
          $width = "180";
          $height = "46";
          break;
      }

      if($this->variant == false) {
        switch ($variant) {
          case false:
              $this->variant = "disabled";
              break;
          case true:
          default:
              $this->variant = "text";
              break;
        }
      }

      
      $data = "<div style=\"width: ".$width."px\">";
      if ($this->variant == "text") {
        $data .= "<div align=center><form method=\"POST\" action=\"". 
                $this->checkout_url . "\"" . ($this->googleAnalytics_id?
                " onsubmit=\"setUrchinInputCode();\"":"") . ">
                <input type=\"hidden\" name=\"cart\" value=\"". 
                base64_encode($this->GetXML()) ."\">
                <input type=\"hidden\" name=\"signature\" value=\"". 
                base64_encode($this->CalcHmacSha1($this->GetXML())). "\"> 
                <input type=\"image\" name=\"Checkout\" alt=\"Checkout\" 
                src=\"". $this->server_url."buttons/checkout.gif?merchant_id=" .
                $this->merchant_id."&w=".$width. "&h=".$height."&style=".
                $style."&variant=".$this->variant."&loc=".$loc."\" 
                height=\"".$height."\" width=\"".$width. "\" />";
                
        if($this->googleAnalytics_id) {
          $data .= "<input type=\"hidden\" name=\"analyticsdata\" value=\"\">";
        }                
        $data .= "</form></div>";
        if($this->googleAnalytics_id) {                
            $data .= "<!-- Start Google analytics -->
            <script src=\"https://ssl.google-analytics.com/urchin.js\" type=\"".
                "text/javascript\">
            </script>
            <script type=\"text/javascript\">
            _uacct = \"" . $this->googleAnalytics_id . "\";
            urchinTracker();
            </script>
            <script src=\"https://checkout.google.com/files/digital/urchin_po" .
                "st.js\" type=\"text/javascript\"></script>  
            <!-- End Google analytics -->";
        }
      } else {
        $data .= "<div><img alt=\"Checkout\" src=\"" .
            "". $this->server_url."buttons/checkout.gif?merchant_id=" .
            "".$this->merchant_id."&w=".$width. "&h=".$height."&style=".$style.
            "&variant=".$this->variant."&loc=".$loc."\" height=\"".$height."\"".
            " width=\"".$width. "\" /></div>";
      }
      if($showtext) {
        $data .="<div align=\"center\"><a href=\"javascript:void(window.ope".
          "n('http://checkout.google.com/seller/what_is_google_checkout.html'" .
          ",'whatischeckout','scrollbars=0,resizable=1,directories=0,height=2" .
          "50,width=400'));\" onmouseover=\"return window.status = 'What is G" .
          "oogle Checkout?'\" onmouseout=\"return window.status = ''\"><font " .
          "size=\"-2\">What is Google Checkout?</font></a></div>";
      }
      $data .= "</div>";
      return $data;
    }
        //Code for generating Checkout button 
    //@param $variant will be ignored if SetButtonVariant() was used before
    function CheckoutButtonNowCode($size="large", $variant=true, $loc="en_US",
                                               $showtext=true, $style="trans") {

      switch (strtolower($size)) {
        case "small":
          $width = "121";
          $height = "44";
          break;
        case "large":
        default:
          $width = "117";
          $height = "48";
          break;
      }

      if($this->variant == false) {
        switch ($variant) {
          case false:
              $this->variant = "disabled";
              break;
          case true:
          default:
              $this->variant = "text";
              break;
        }
      }


      
      $data = "<div style=\"width: ".$width."px\">";
      if ($this->variant == "text") {
        $data .= "<div align=center><form method=\"POST\" action=\"". 
                $this->checkout_url . "\"" . ($this->googleAnalytics_id?
                " onsubmit=\"setUrchinInputCode();\"":"") . ">
                <input type=\"hidden\" name=\"buyButtonCart\" value=\"". 
                base64_encode($this->GetXML()) ."//separator//" .
                base64_encode($this->CalcHmacSha1($this->GetXML())) . "\">
                <input type=\"image\" name=\"Checkout\" alt=\"BuyNow\" 
                src=\"". $this->server_url."buttons/buy.gif?merchant_id=" .
                $this->merchant_id."&w=".$width. "&h=".$height."&style=".
                $style."&variant=".$this->variant."&loc=".$loc."\" 
                height=\"".$height."\" width=\"".$width. "\" />";
                
        if($this->googleAnalytics_id) {
          $data .= "<input type=\"hidden\" name=\"analyticsdata\" value=\"\">";
        }                
        $data .= "</form></div>";
        if($this->googleAnalytics_id) {                
            $data .= "<!-- Start Google analytics -->
            <script src=\"https://ssl.google-analytics.com/urchin.js\" type=\"".
                "text/javascript\">
            </script>
            <script type=\"text/javascript\">
            _uacct = \"" . $this->googleAnalytics_id . "\";
            urchinTracker();
            </script>
            <script src=\"https://checkout.google.com/files/digital/urchin_po" .
                "st.js\" type=\"text/javascript\"></script>  
            <!-- End Google analytics -->";
        }
//        ask for link to BuyNow disable button
      } else {
        $data .= "<div><img alt=\"Checkout\" src=\"" .
            "". $this->server_url."buttons/buy.gif?merchant_id=" .
            "".$this->merchant_id."&w=".$width. "&h=".$height."&style=".$style.
            "&variant=".$this->variant."&loc=".$loc."\" height=\"".$height."\"".
            " width=\"".$width. "\" /></div>";
      }
      if($showtext) {
        $data .="<div align=\"center\"><a href=\"javascript:void(window.ope".
          "n('http://checkout.google.com/seller/what_is_google_checkout.html'" .
          ",'whatischeckout','scrollbars=0,resizable=1,directories=0,height=2" .
          "50,width=400'));\" onmouseover=\"return window.status = 'What is G" .
          "oogle Checkout?'\" onmouseout=\"return window.status = ''\"><font " .
          "size=\"-2\">What is Google Checkout?</font></a></div>";
      }
      $data .= "</div>";
      return $data;
    }
    

    /**
     * Get the Google Checkout button's html to be used with the html api.
     * 
     * {@link http://code.google.com/apis/checkout/developer/index.html#google_checkout_buttons}
     * 
     * @param string $size the size of the button, one of 'large', 'medium' or
     *                     'small'.
     *                     defaults to 'large'
     * @param bool $variant true for an enabled button, false for a 
     *                      disabled one. defaults to true. will be ignored if
     *                      SetButtonVariant() was used before.
     * @param string $loc the locale of the button's text, the only valid value
     *                    is 'en_US' (used as default)
     * @param bool $showtext whether to show Google Checkout text or not, 
     *                       defaults to true.
     * @param string $style the background style of the button, one of 'white'
     *                      or 'trans'. defaults to "trans"
     * 
     * @return string the button's html
     */
    function CheckoutHTMLButtonCode($size="large", $variant=true, $loc="en_US",
                                               $showtext=true, $style="trans") {

      switch (strtolower($size)) {
        case "medium":
          $width = "168";
          $height = "44";
          break;

        case "small":
          $width = "160";
          $height = "43";
          break;
        case "large":
        default:
          $width = "180";
          $height = "46";
          break;
      }

      if($this->variant == false) {
        switch ($variant) {
          case false:
              $this->variant = "disabled";
              break;
          case true:
          default:
              $this->variant = "text";
              break;
        }
      }

      
      $data = "<div style=\"width: ".$width."px\">";
      if ($this->variant == "text") {
        $data .= "<div align=\"center\"><form method=\"POST\" action=\"". 
                $this->checkoutForm_url . "\"" . ($this->googleAnalytics_id?
                " onsubmit=\"setUrchinInputCode();\"":"") . ">";

        $request = $this->GetXML();
        require_once('xml-processing/gc_xmlparser.php');
        $xml_parser = new gc_xmlparser($request);
        $root = $xml_parser->GetRoot();
        $XMLdata = $xml_parser->GetData();
        $this->xml2html($XMLdata[$root], '', $data);
        $data .= "<input type=\"image\" name=\"Checkout\" alt=\"Checkout\" " .
                "src=\"". $this->server_url."buttons/checkout.gif?merchant_id=".
                $this->merchant_id."&w=".$width. "&h=".$height."&style=".
                $style."&variant=".$this->variant."&loc=".$loc."\" 
                height=\"".$height."\" width=\"".$width. "\" />";
                
        if($this->googleAnalytics_id) {
          $data .= "<input type=\"hidden\" name=\"analyticsdata\" value=\"\">";
        }                
        $data .= "</form></div>";
        if($this->googleAnalytics_id) {                
            $data .= "<!-- Start Google analytics -->
            <script src=\"https://ssl.google-analytics.com/urchin.js\" type=\"".
                "text/javascript\">
            </script>
            <script type=\"text/javascript\">
            _uacct = \"" . $this->googleAnalytics_id . "\";
            urchinTracker();
            </script>
            <script src=\"https://checkout.google.com/files/digital/urchin_po" .
                "st.js\" type=\"text/javascript\"></script>  
            <!-- End Google analytics -->";
        }
      } else {
        $data .= "<div align=\"center\"><img alt=\"Checkout\" src=\"" .
            "". $this->server_url."buttons/checkout.gif?merchant_id=" .
            "".$this->merchant_id."&w=".$width. "&h=".$height."&style=".$style.
            "&variant=".$this->variant."&loc=".$loc."\" height=\"".$height."\"".
            " width=\"".$width. "\" /></div>";
      }
      if($showtext){
        $data .= "<div align=\"center\"><a href=\"javascript:void(window.ope" .
          "n('http://checkout.google.com/seller/what_is_google_checkout.html'" .
          ",'whatischeckout','scrollbars=0,resizable=1,directories=0,height=2" .
          "50,width=400'));\" onmouseover=\"return window.status = 'What is G" .
          "oogle Checkout?'\" onmouseout=\"return window.status = ''\"><font " .
          "size=\"-2\">What is Google Checkout?</font></a></div>";
      }
      $data .= "</div>";


      return $data;
      
    }

    /**
     * @access private
     */
    function xml2html($data, $path = '', &$rta){
//      global $multiple_tags,$ignore_tags;
    //    $arr = gc_get_arr_result($data);  
      foreach($data as $tag_name => $tag) {
        if(isset($this->ignore_tags[$tag_name])){
          continue;
        }
        if(is_array($tag)){
    //     echo print_r($tag, true) . $tag_name . "<- tag name\n";
          if(!$this->is_associative_array($data)) {
            $new_path = $path . '-' . ($tag_name +1);
          } else {
            if(isset($this->multiple_tags[$tag_name])
                && $this->is_associative_array($tag) 
                && !$this->isChildOf($path, $this->multiple_tags[$tag_name])){
              $tag_name .= '-1'; 
            }
            $new_path = $path . (empty($path)?'':'.') . $tag_name;
          }
          $this->xml2html($tag, $new_path, $rta);
        }
        else {
          $new_path = $path;
          if($tag_name != 'VALUE'){
            $new_path = $path . "." . $tag_name;  
          }
          $rta .= '<input type="hidden" name="' .
                    $new_path . '" value="' .$tag . '"/>'."\n";
        }
      }
    }
        
    // Returns true if a given variable represents an associative array
    /**
     * @access private
     */
    function is_associative_array($var) {
      return is_array($var) && !is_numeric(implode('', array_keys($var)));
    } 
    
    /**
     * @access private
     */
    function isChildOf($path='', $parents=array()){
      $intersect =array_intersect(explode('.',$path), $parents); 
      return !empty($intersect);  
    }

    /**
     * Get the Google Checkout acceptance logos html
     * 
     * {@link http://checkout.google.com/seller/acceptance_logos.html}
     * 
     * @param integer $type the acceptance logo type, valid values: 1, 2, 3
     * 
     * @return string the logo's html
     */
    function CheckoutAcceptanceLogo($type=1) {
      switch ($type) {
        case 2:
            return '<link rel="stylesheet" href="https://checkout.google.com/' .
                'seller/accept/s.css" type="text/css" media="screen" /><scrip' .
                't type="text/javascript" src="https://checkout.google.com/se' .
                'ller/accept/j.js"></script><script type="text/javascript">sh' .
                'owMark(1);</script><noscript><img src="https://checkout.goog' .
                'le.com/seller/accept/images/st.gif" width="92" height="88" a' .
                'lt="Google Checkout Acceptance Mark" /></noscript>';
          break;
        case 3:
            return '<link rel="stylesheet" href="https://checkout.google.com/' .
                'seller/accept/s.css" type="text/css" media="screen" /><scrip' .
                't type="text/javascript" src="https://checkout.google.com/se' .
                'ller/accept/j.js"></script><script type="text/javascript">sh' .
                'owMark(2);</script><noscript><img src="https://checkout.goog' .
                'le.com/seller/accept/images/ht.gif" width="182" height="44" ' .
                'alt="Google Checkout Acceptance Mark" /></noscript>';
          break;
        case 1:
      	default:
            return '<link rel="stylesheet" href="https://checkout.google.com/' .
                'seller/accept/s.css" type="text/css" media="screen" /><scrip' .
                't type="text/javascript" src="https://checkout.google.com/se' .
                'ller/accept/j.js"></script><script type="text/javascript">sh' .
                'owMark(3);</script><noscript><img src="https://checkout.goog' .
                'le.com/seller/accept/images/sc.gif" width="72" height="73" a' .
                'lt="Google Checkout Acceptance Mark" /></noscript>';
      		break;
      }
    }

    /**
     * Calculates the cart's hmac-sha1 signature, this allows google to verify 
     * that the cart hasn't been tampered by a third-party.
     * 
     * {@link http://code.google.com/apis/checkout/developer/index.html#create_signature}
     * 
     * @param string $data the cart's xml
     * @return string the cart's signature (in binary format)
     */
    function CalcHmacSha1($data) {
      $key = $this->merchant_key;
      $blocksize = 64;
      $hashfunc = 'sha1';
      if (strlen($key) > $blocksize) {
        $key = pack('H*', $hashfunc($key));
      }
      $key = str_pad($key, $blocksize, chr(0x00));
      $ipad = str_repeat(chr(0x36), $blocksize);
      $opad = str_repeat(chr(0x5c), $blocksize);
      $hmac = pack(
                    'H*', $hashfunc(
                            ($key^$opad).pack(
                                    'H*', $hashfunc(
                                            ($key^$ipad).$data
                                    )
                            )
                    )
                );
      return $hmac; 
    }

    //Method used internally to set true/false cart variables
    /**
     * @access private
     */
    function _GetBooleanValue($value, $default) {
      switch(strtolower($value)){
         case "true":
          return "true";
         break;
         case "false":
          return"false";
         break;
         default:
          return $default;
         break;
      }
    }
    //Method used internally to set true/false cart variables
    // Deprecated, must NOT use eval, bug-prune function
    /**
     * @access private
     */
    function _SetBooleanValue($string, $value, $default) {
      $value = strtolower($value);
      if($value == "true" || $value == "false")
        eval('$this->'.$string.'="'.$value.'";');
      else
        eval('$this->'.$string.'="'.$default.'";');
    }
  }
  
  /**
   * @abstract
   * Abstract class that represents the merchant-private-data.
   * 
   * See {@link MerchantPrivateData} and {@link MerchantPrivateItemData}
   * 
   * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_merchant-private-data <merchant-private-data>}
   */
  class MerchantPrivate {
    var $data;
    var $type = "Abstract";
    function MerchantPrivate() {
    }
    
    function AddMerchantPrivateToXML(&$xml_data) {
      if(is_array($this->data)) {
        $xml_data->Push($this->type);
        $this->_recursiveAdd($xml_data, $this->data);
        $xml_data->Pop($this->type);
      }
      else {
        $xml_data->Element($this->type, (string)$this->data);
      }
    }
    
    /**
     * @access private
     */
    function _recursiveAdd(&$xml_data, $data){
      foreach($data as $name => $value) {
        if(is_array($value)) {
          $xml_data->Push($name);
          $this->_recursiveAdd($xml_data, $name);
          $xml_data->Pop($name);        
        }
        else {
          $xml_data->Element($name, (string)$value);
        }
      }
    }
  }
  
  /**
   * Class that represents the merchant-private-data.
   * 
   * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_merchant-private-data <merchant-private-data>}
   */
  class MerchantPrivateData extends MerchantPrivate {
    /**
     * @param mixed $data a string with the data that will go in the 
     *                    merchant-private-data tag or an array that will
     *                    be mapped to xml, formatted like (e.g.):
     *                    array('my-order-id' => 34234,
     *                          'stuff' => array('registered' => 'yes',
     *                                           'category' => 'hip stuff'))
     *                    this will map to:
     *                    <my-order-id>
     *                      <stuff>
     *                        <registered>yes</registered>
     *                        <category>hip stuff</category>
     *                      </stuff>
     *                    </my-order-id>
     */
    function MerchantPrivateData($data = array()) {
      $this->data = $data;
      $this->type = 'merchant-private-data';
    }
  }

  /**
   * Class that represents a merchant-private-item-data.
   * 
   * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_merchant-private-item-data <merchant-private-data>}
   */
  class MerchantPrivateItemData extends MerchantPrivate {
    /**
     * @param mixed $data a string with the data that will go in the 
     *                    merchant-private-item-data tag or an array that will
     *                    be mapped to xml, formatted like:
     *                    array('my-item-id' => 34234,
     *                          'stuff' => array('label' => 'cool',
     *                                           'category' => 'hip stuff'))
     *                    this will map to:
     *                    <my-item-id>
     *                      <stuff>
     *                        <label>cool</label>
     *                        <category>hip stuff</category>
     *                      </stuff>
     *                    </my-item-id>
     */
    function MerchantPrivateItemData($data = array()) {
      $this->data = $data;
      $this->type = 'merchant-private-item-data';
    }
  }
?>