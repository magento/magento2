<?php

/*
 * Copyright (C) 2006 Google Inc.
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
  * Used to create a Google Checkout result as a response to a 
  * merchant-calculations feedback structure, i.e shipping, tax, coupons and
  * gift certificates.
  * 
  * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_result <result>}
  */
  // refer to demo/responsehandlerdemo.php for usage of this code
  class GoogleResult {
    var $shipping_name;
    var $address_id;
    var $shippable;
    var $ship_price;

    var $tax_amount;

    var $coupon_arr = array();
    var $giftcert_arr = array();

    /**
     * @param integer $address_id the id of the anonymous address sent by 
     *                           Google Checkout.
     */
    function GoogleResult($address_id) {
      $this->address_id = $address_id;
    }

    function SetShippingDetails($name, $price, $shippable = "true") {
      $this->shipping_name = $name;
      $this->ship_price = $price;
      $this->shippable = $shippable;
    }

    function SetTaxDetails($amount) {
      $this->tax_amount = $amount;
    }

    function AddCoupons($coupon) {
      $this->coupon_arr[] = $coupon;
    }

    function AddGiftCertificates($gift) {
      $this->giftcert_arr[] = $gift;
    }
  }

 /**
  * This is a class used to return the results of coupons the buyer supplied in
  * the order page.
  * 
  * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_coupon-result <coupon-result>}
  */
  class GoogleCoupons {
    var $coupon_valid;
    var $coupon_code;
    var $coupon_amount;
    var $coupon_message;

    function googlecoupons($valid, $code, $amount, $message) {
      $this->coupon_valid = $valid;
      $this->coupon_code = $code;
      $this->coupon_amount = $amount;
      $this->coupon_message = $message;
    } 
  }

 /**
  * This is a class used to return the results of gift certificates
  * supplied by the buyer on the place order page
  * 
  * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_gift-certificate-result} <gift-certificate-result>
  */
  
  class GoogleGiftcerts {
    var $gift_valid;
    var $gift_code;
    var $gift_amount;
    var $gift_message;

    function googlegiftcerts($valid, $code, $amount, $message) {
      $this->gift_valid = $valid;
      $this->gift_code = $code;
      $this->gift_amount = $amount;
      $this->gift_message = $message;
    }
  }
?>
