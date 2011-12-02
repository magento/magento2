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

 /* This class is instantiated everytime any notification or
  * order processing commands are received.
  *
  * Refer demo/responsehandlerdemo.php for different use case scenarios
  * for this code
  */


  /**
   * Handles the response to notifications sent by the Google Checkout server.
   */
  class GoogleResponse {
    var $merchant_id;
    var $merchant_key;
    var $schema_url;
    var $serial_number;

    var $log;
    var $response;
    var $root='';
    var $data=array();
    var $xml_parser;

    /**
     * @param string $id the merchant id
     * @param string $key the merchant key
     */
    function GoogleResponse($id=null, $key=null) {
      $this->merchant_id = $id;
      $this->merchant_key = $key;
      $this->schema_url = "http://checkout.google.com/schema/2";
      #ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
      require_once('googlelog.php');
      $this->log = new GoogleLog('', '', L_OFF);
    }

    function setSerialNumber($num)
    {
        $this->serial_number = $num;
    }

    /**
     * @param string $id the merchant id
     * @param string $key the merchant key
     */
    function SetMerchantAuthentication($id, $key){
      $this->merchant_id = $id;
      $this->merchant_key = $key;
    }

    function SetLogFiles($errorLogFile, $messageLogFile, $logLevel=L_ERR_RQST) {
      $this->log = new GoogleLog($errorLogFile, $messageLogFile, $logLevel);
    }

    /**
     * Verifies that the authentication sent by Google Checkout matches the
     * merchant id and key
     *
     * @param string $headers the headers from the request
     */
    function HttpAuthentication($headers=null, $die=true) {
      if(!is_null($headers)) {
        $_SERVER = $headers;
      }
      // moshe's fix for CGI
      if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
          foreach ($_SERVER as $k=>$v) {
              if (substr($k, -18)==='HTTP_AUTHORIZATION' && !empty($v)) {
                  $_SERVER['HTTP_AUTHORIZATION'] = $v;
                  break;
              }
          }
      }

      if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $compare_mer_id = $_SERVER['PHP_AUTH_USER'];
        $compare_mer_key = $_SERVER['PHP_AUTH_PW'];
      }

  //  IIS Note::  For HTTP Authentication to work with IIS,
  // the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
      else if(isset($_SERVER['HTTP_AUTHORIZATION'])){
        list($compare_mer_id, $compare_mer_key) = explode(':',
            base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'],
            strpos($_SERVER['HTTP_AUTHORIZATION'], " ") + 1)));
      } else if(isset($_SERVER['Authorization'])) {
        list($compare_mer_id, $compare_mer_key) = explode(':',
            base64_decode(substr($_SERVER['Authorization'],
            strpos($_SERVER['Authorization'], " ") + 1)));
      } else {
        $this->SendFailAuthenticationStatus(
              "Failed to Get Basic Authentication Headers",$die);
        return false;
      }
      if($compare_mer_id != $this->merchant_id
         || $compare_mer_key != $this->merchant_key) {
        $this->SendFailAuthenticationStatus("Invalid Merchant Id/Key Pair",$die);
        return false;
      }
      return true;
    }

    function ProcessMerchantCalculations($merchant_calc) {
      $this->SendOKStatus();
      $result = $merchant_calc->GetXML();
      echo $result;
    }

// Notification API
    function ProcessNewOrderNotification() {
      $this->SendAck();
    }
    function ProcessRiskInformationNotification() {
      $this->SendAck();
    }
    function ProcessOrderStateChangeNotification() {
      $this->SendAck();
    }
//   Amount Notifications
    function ProcessChargeAmountNotification() {
      $this->SendAck();
    }
    function ProcessRefundAmountNotification() {
      $this->SendAck();
    }
    function ProcessChargebackAmountNotification() {
      $this->SendAck();
    }
    function ProcessAuthorizationAmountNotification() {
      $this->SendAck();
    }

    function SendOKStatus() {
      header('HTTP/1.0 200 OK');
    }

    /**
     * Set the response header indicating an erroneous authentication from
     * Google Checkout
     *
     * @param string $msg the message to log
     */
    function SendFailAuthenticationStatus($msg="401 Unauthorized Access",
                                                                   $die=true) {
      $this->log->logError($msg);
      header('WWW-Authenticate: Basic realm="GoogleCheckout API Callback"');
      header('HTTP/1.0 401 Unauthorized');
      if($die) {
       die($msg);
      } else {
      echo $msg;
      }
    }

    /**
     * Set the response header indicating a malformed request from Google
     * Checkout
     *
     * @param string $msg the message to log
     */
    function SendBadRequestStatus($msg="400 Bad Request", $die=true) {
      $this->log->logError($msg);
      header('HTTP/1.0 400 Bad Request');
      if($die) {
       die($msg);
      } else {
      echo $msg;
      }
    }

    /**
     * Set the response header indicating that an internal error ocurred and
     * the notification sent by Google Checkout can't be processed right now
     *
     * @param string $msg the message to log
     */
    function SendServerErrorStatus($msg="500 Internal Server Error",
                                                                   $die=true) {
      $this->log->logError($msg);
      header('HTTP/1.0 500 Internal Server Error');
      if($die) {
       die($msg);
      } else {
        echo $msg;
      }
    }

    /**
     * Send an acknowledgement in response to Google Checkout's request
     */
    function SendAck($die=false) {
      $this->SendOKStatus();
      $acknowledgment = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
                        "<notification-acknowledgment xmlns=\"" .
                        $this->schema_url . "\" serial-number=\"" .
                        $this->serial_number . "\"/>";
      $this->log->LogResponse($acknowledgment);
      if($die) {
        die($acknowledgment);
      } else {
        echo $acknowledgment;
      }
    }

    /**
     * @access private
     */
    function GetParsedXML($request=null){
      if(!is_null($request)) {
        $this->log->LogRequest($request);
        $this->response = $request;
        #ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
        require_once('xml-processing/gc_xmlparser.php');

        $this->xml_parser = new gc_xmlparser($request);
        $this->root = $this->xml_parser->GetRoot();
        $this->data = $this->xml_parser->GetData();
      }
      return array($this->root, $this->data);
    }
  }
?>