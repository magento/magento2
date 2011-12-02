<?php
// Distributed by license from CardinalCommerce Corporation
/////////////////////////////////////////////////////////////////////////////////////////////
//  CardinalCommerce (http://www.cardinalcommerce.com)
//  CentinelClient.php
//  Version 1.2 02/17/2005
//
//	Usage
//		The CentinelClient class is defined to assist integration efforts with the Centinel
//		XML message integration. The class implements helper methods to construct, send, and
//		receive XML messages with respect to the Centinel XML Message APIs.
//
/////////////////////////////////////////////////////////////////////////////////////////////

    require("XMLParser.php");
	include "CentinelErrors.php";

    class CentinelClient {

		var $request ;
		var $response ;
		var $parser;

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function Add(name, value)
		//
		// Add name/value pairs to the Centinel request collection.
		/////////////////////////////////////////////////////////////////////////////////////////////


		function add($name, $value) {
			 $this->request[$name] = $this->escapeXML($value);
		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function getValue(name)
		//
		// Retrieve a specific value for the give name within the Centinel response collection.
		/////////////////////////////////////////////////////////////////////////////////////////////


		function getValue($name) {
		    if (isset($this->response[$name])) {
                return $this->response[$name];
		    }else{
		        return "";
		    }
		}


		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function getRequestXml(name)
		//
		// Serialize all elements of the request collection into a XML message, and format the required
		// form payload according to the Centinel XML Message APIs. The form payload is returned from
		// the function.
		/////////////////////////////////////////////////////////////////////////////////////////////


		function getRequestXml(){
			$queryString = "<CardinalMPI>";
			foreach ($this->request as $name => $value) {
				$queryString = $queryString."<".($name).">".($value)."</".($name).">" ;
			}
			$queryString = $queryString."</CardinalMPI>";
			return "cmpi_msg=".urlencode($queryString);
		}

	    /////////////////////////////////////////////////////////////////////////////////////////////
		// Function sendHttp(url, "", $timeout)
		//
		// HTTP POST the form payload to the url using cURL.
		// form payload according to the Centinel XML Message APIs. The form payload is returned from
		// the function.
		/////////////////////////////////////////////////////////////////////////////////////////////

		function sendHttp($url, $connectTimeout="", $timeout) {

		    // verify that the URL uses a supported protocol.

			if( (strpos($url, "http://")=== 0) || (strpos($url, "https://")=== 0) ) {

				//Construct the payload to POST to the url.

				$data = $this->getRequestXml();
				// create a new cURL resource

				$ch = curl_init($url);

				// set URL and other appropriate options
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

				// Execute the request.

				$result = curl_exec($ch);
				$succeeded  = curl_errno($ch) == 0 ? true : false;

				// close cURL resource, and free up system resources

				curl_close($ch);

				// If Communication was not successful set error result, otherwise

				if(!$succeeded) {

					$result = $this->setErrorResponse(CENTINEL_ERROR_CODE_8030, CENTINEL_ERROR_CODE_8030_DESC);

				}

				// Assert that we received an expected Centinel Message in reponse.

				if (strpos($result, "<CardinalMPI>") === false) {
					$result = $this->setErrorResponse(CENTINEL_ERROR_CODE_8010, CENTINEL_ERROR_CODE_8010_DESC);
				}


			} else {
				$result = $this->setErrorResponse(CENTINEL_ERROR_CODE_8000, CENTINEL_ERROR_CODE_8000_DESC);
			}
			$parser = new XMLParser;
			$parser->deserializeXml($result);
			$this->response = $parser->deserializedResponse;
		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function setErrorResponse(errorNo, errorDesc)
		//
		// Initialize an Error response to ensure that parsing will be handled properly.
		/////////////////////////////////////////////////////////////////////////////////////////////

		function setErrorResponse($errorNo, $errorDesc) {

		  $resultText  = "<CardinalMPI>";
		  $resultText = $resultText."<ErrorNo>".($errorNo)."</ErrorNo>" ;
		  $resultText = $resultText."<ErrorDesc>".($errorDesc)."</ErrorDesc>" ;
		  $resultText  = $resultText."</CardinalMPI>";

		  return $resultText;
		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function escapeXML(value)
		//
		// Escaped string converting all '&' to '&amp;' and all '<' to '&lt'. Return the escaped value.
		/////////////////////////////////////////////////////////////////////////////////////////////

		function escapeXML($elementValue){

			$escapedValue = str_replace("&", "&amp;", $elementValue);
			$escapedValue = str_replace("<", "&lt;", $escapedValue);

			return $escapedValue;

		}

	}
