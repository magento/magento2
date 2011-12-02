<?php
/*
  Copyright (C) 2006 Google Inc.

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Classes used to generate XML data
 * Based on sample code available at http://simon.incutio.com/code/php/XmlWriter.class.php.txt 
 */

  /**
   * Generates xml data
   */
  class gc_XmlBuilder {
    var $xml;
    var $indent;
    var $stack = array();

    function gc_XmlBuilder($indent = '  ') {
      $this->indent = $indent;
      $this->xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
    }

    function _indent() {
      for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
        $this->xml .= $this->indent;
      }
    }

    //Used when an element has sub-elements
    // This function adds an open tag to the output
    function Push($element, $attributes = array()) {
      $this->_indent();
      $this->xml .= '<'.$element;
      foreach ($attributes as $key => $value) {
        $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
      }
      $this->xml .= ">\n";
      $this->stack[] = $element;
    }

    //Used when an element has no subelements.
    //Data within the open and close tags are provided with the 
    //contents variable
    function Element($element, $content, $attributes = array()) {
      $this->_indent();
      $this->xml .= '<'.$element;
      foreach ($attributes as $key => $value) {
        $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
      }
      $this->xml .= '>'.htmlentities($content).'</'.$element.'>'."\n";
    }

    function EmptyElement($element, $attributes = array()) {
      $this->_indent();
      $this->xml .= '<'.$element;
      foreach ($attributes as $key => $value) {
        $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
      }
      $this->xml .= " />\n";
    }

    //Used to close an open tag
    function Pop($pop_element) {
      $element = array_pop($this->stack);
      $this->_indent();
      if($element !== $pop_element) 
        die('XML Error: Tag Mismatch when trying to close "'. $pop_element. '"');
      else
        $this->xml .= "</$element>\n";
    }

    function GetXML() {
      if(count($this->stack) != 0)
        die ('XML Error: No matching closing tag found for " '. array_pop($this->stack). '"');
      else
        return $this->xml;
    }
  }
?>
