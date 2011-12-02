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
  * Classes used to handle tax rules and tables
  */

  /**
   * Represents a tax rule
   * 
   * @see GoogleDefaultTaxRule
   * @see GoogleAlternateTaxRule
   * 
   * @abstract
   */
  class GoogleTaxRule {

    var $tax_rate;

    var $world_area = false;
    var $country_codes_arr;
    var $postal_patterns_arr;
    var $state_areas_arr;
    var $zip_patterns_arr;
    var $country_area;

    function GoogleTaxRule() {
    }

    function SetWorldArea($world_area = true) {
      $this->world_area = $world_area;
    }

    function AddPostalArea($country_code, $postal_pattern = "") {
      $this->country_codes_arr[] = $country_code;
      $this->postal_patterns_arr[]= $postal_pattern;
    }

    function SetStateAreas($areas) {
      if(is_array($areas))
        $this->state_areas_arr = $areas;
      else
        $this->state_areas_arr = array($areas);
    }

    function SetZipPatterns($zips) {
      if(is_array($zips))
        $this->zip_patterns_arr = $zips;
      else
        $this->zip_patterns_arr = array($zips);
    }

    function SetCountryArea($country_area) {
      switch ($country_area) {
        case "CONTINENTAL_48":
        case "FULL_50_STATES":
        case "ALL":
          $this->country_area = $country_area;
        break;
        default:
          $this->country_area = "";
        break;
      }
    }
  }

  /**
   * Represents a default tax rule
   * 
   * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_default-tax-rule <default-tax-rule>}
   */
  class GoogleDefaultTaxRule extends GoogleTaxRule {

    var $shipping_taxed = false;

    function GoogleDefaultTaxRule($tax_rate, $shipping_taxed = "false") {
      $this->tax_rate = $tax_rate;
      $this->shipping_taxed= $shipping_taxed;

      $this->country_codes_arr = array();
      $this->postal_patterns_arr = array();
      $this->state_areas_arr = array();
      $this->zip_patterns_arr = array();
    }
  }
  
  /**
   * Represents an alternate tax rule
   * 
   * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_alternate-tax-rule <alternate-tax-rule>}
   */
  class GoogleAlternateTaxRule extends GoogleTaxRule {

    function GoogleAlternateTaxRule($tax_rate) {
      $this->tax_rate = $tax_rate;

      $this->country_codes_arr = array();
      $this->postal_patterns_arr = array();
      $this->state_areas_arr = array();
      $this->zip_patterns_arr = array();
    }

  }


  /**
   * Represents an alternate tax table
   * 
   * GC tag: {@link http://code.google.com/apis/checkout/developer/index.html#tag_alternate-tax-table <alternate-tax-table>}
   */
  class GoogleAlternateTaxTable {

    var $name;
    var $tax_rules_arr;
    var $standalone;

    function GoogleAlternateTaxTable($name = "", $standalone = "false") {
      if($name != "") {
        $this->name = $name;
        $this->tax_rules_arr = array();
        $this->standalone = $standalone;
      }
    }

    function AddAlternateTaxRules($rules) {
      $this->tax_rules_arr[] = $rules;
    }
  }


?>
