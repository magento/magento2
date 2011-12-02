<?php
// Distributed by license from CardinalCommerce Corporation
/////////////////////////////////////////////////////////////////////////////////////////////
//  CardinalCommerce (http://www.cardinalcommerce.com)
//  XMLParser.php
//  Version 1.2 02/17/2005
//
//	Usage
//		XML Parser class to assist with the parsing of the XML messages received from the MAPS
//		Server. Wraps core PHP XML functions.
//
/////////////////////////////////////////////////////////////////////////////////////////////

  class XMLParser{

		var $xml_parser;
		var $deseralizedResponse;
		var $elementName;
		var $elementValue;

	    /////////////////////////////////////////////////////////////////////////////////////////////
		// Function XMLParser()
		//
		// Initialize the XML parser.
		/////////////////////////////////////////////////////////////////////////////////////////////

		function XMLParser() {
		  $this->xml_parser = xml_parser_create();
		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function startElement(parser, name, attribute)
		//
		// Start Tag Element Handler
		/////////////////////////////////////////////////////////////////////////////////////////////

		function startElement($parser, $name, $attrs='') {
			$this->elementName= $name;

		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function elementData(parser, data)
		//
		// Element Data Handler
		/////////////////////////////////////////////////////////////////////////////////////////////

		function elementData($parser, $data) {
			$this->elementValue .= $data;

		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function endElement(name, value)
		//
		// End Tag Element Handler
		/////////////////////////////////////////////////////////////////////////////////////////////

		function endElement($parser, $name) {

			 $this->deserializedResponse[$this->elementName]= $this->elementValue;
			 $this->elementName = "";
			 $this->elementValue = "";
		}

		/////////////////////////////////////////////////////////////////////////////////////////////
		// Function deserialize(xmlString)
		//
		// Deserilize the XML reponse message and add each element to the deseralizedResponse collection.
		// Once complete, then each element reference will be available using the getValue function.
		/////////////////////////////////////////////////////////////////////////////////////////////

		function deserializeXml($responseString) {

			  xml_set_object($this->xml_parser, $this);
			  xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING,FALSE);
			  xml_set_element_handler($this->xml_parser, "startElement", "endElement");
			  xml_set_character_data_handler($this->xml_parser, "elementData");

			  if (!xml_parse($this->xml_parser, $responseString)) {

					$this->deserializedResponse["ErrorNo"]= CENTINEL_ERROR_CODE_8020;
					$this->deserializedResponse["ErrorDesc"]= CENTINEL_ERROR_CODE_8020_DESC;
			  }

			  xml_parser_free($this->xml_parser);
		}
 }
