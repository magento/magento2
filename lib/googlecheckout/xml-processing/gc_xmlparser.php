<?php
/**
 * Classes used to parse xml data
 */
/*
  Copyright (C) 2007 Google Inc.

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

  For more info: http://code.google.com/p/google-checkout-php-sample-code/

  Upgrades (05/23/2007) ropu:
     Remove UpdateRecursive()
     Support for empty tags (like <world-area/>)
     Accept multiple options in a second parameter
*
**/

/* This uses SAX parser to convert XML data into PHP associative arrays
 * When invoking the constructor with the input data, strip out the first XML line
 *
 * Member field Description:
 * $params: This stores the XML data. The attributes and contents of XML tags
 * can be accessed as follows
 *
 * <addresses>
 *  <anonymous-address id="123"> <test>data 1 </test>
 *  </anonymous-address>
 *  <anonymous-address id="456"> <test>data 2 </test>
 *  </anonymous-address>
 * </addresses>
 *
 * print_r($this->params) will return
 Array
(
    [addresses] => Array
        (
            [anonymous-address] => Array
                (
                    [0] => Array
                        (
                            [id] => 123
                            [test] => Array
                                (
                                    [VALUE] => data 1
                                )

                        )

                    [1] => Array
                        (
                            [id] => 456
                            [test] => Array
                                (
                                    [VALUE] => data 2
                                )

                        )

                )

        )

)
  * gc_xmlparser returns an empty params array if it encounters
  * any error during parsing
  */
  // XML to Array
  class gc_xmlparser {

    var $params = array(); //Stores the object representation of XML data
    var $root = NULL;
    var $global_index = -1;
    var $fold = false;

   /* Constructor for the class
    * Takes in XML data as input( do not include the <xml> tag
    */
    function gc_xmlparser($input, $xmlParams=array(XML_OPTION_CASE_FOLDING => 0)) {
      $xmlp = xml_parser_create();
      foreach($xmlParams as $opt => $optVal) {
        switch( $opt ) {
          case XML_OPTION_CASE_FOLDING:
            $this->fold = $optVal;
           break;
          default:
           break;
        }
        xml_parser_set_option($xmlp, $opt, $optVal);
      }

      if(xml_parse_into_struct($xmlp, $input, $vals, $index)) {
        $this->root = $this->_foldCase($vals[0]['tag']);
        $this->params = $this->xml2ary($vals);
      }
      xml_parser_free($xmlp);
    }

    function _foldCase($arg) {
      return( $this->fold ? strtoupper($arg) : $arg);
    }

/*
 * Credits for the structure of this function
 * http://mysrc.blogspot.com/2007/02/php-xml-to-array-and-backwards.html
 *
 * Adapted by Ropu - 05/23/2007
 *
 */
    function xml2ary($vals) {

        $mnary=array();
        $ary=&$mnary;
        foreach ($vals as $r) {
            $t=$r['tag'];
            if ($r['type']=='open') {
                if (isset($ary[$t]) && !empty($ary[$t])) {
                    if (isset($ary[$t][0])){
                      $ary[$t][]=array();
                    }
                    else {
                      $ary[$t]=array($ary[$t], array());
                    }
                    $cv=&$ary[$t][count($ary[$t])-1];
                }
                else {
                  $cv=&$ary[$t];
                }
                $cv=array();
                if (isset($r['attributes'])) {
                  foreach ($r['attributes'] as $k=>$v) {
                    $cv[$k]=$v;
                  }
                }

                $cv['_p']=&$ary;
                $ary=&$cv;

            } else if ($r['type']=='complete') {
                if (isset($ary[$t]) && !empty($ary[$t])) { // same as open
                    if (isset($ary[$t][0])) {
                      $ary[$t][]=array();
                    }
                    else {
                      $ary[$t]=array($ary[$t], array());
                    }
                    $cv=&$ary[$t][count($ary[$t])-1];
                }
                else {
                  $cv=&$ary[$t];
                }
                if (isset($r['attributes'])) {
                  foreach ($r['attributes'] as $k=>$v) {
                    $cv[$k]=$v;
                  }
                }
                $cv['VALUE'] = (isset($r['value']) ? $r['value'] : '');

            } elseif ($r['type']=='close') {
                $ary=&$ary['_p'];
            }
        }

        $this->_del_p($mnary);
        return $mnary;
    }

    // _Internal: Remove recursion in result array
    function _del_p(&$ary) {
        foreach ($ary as $k=>$v) {
            if ($k==='_p') {
              unset($ary[$k]);
            }
            else if(is_array($ary[$k])) {
              $this->_del_p($ary[$k]);
            }
        }
    }

    /* Returns the root of the XML data */
    function GetRoot() {
      return $this->root;
    }

    /* Returns the array representing the XML data */
    function GetData() {
      return $this->params;
    }
  }

  /* In case the XML API contains multiple open tags
     with the same value, then invoke this function and
     perform a foreach on the resultant array.
     This takes care of cases when there is only one unique tag
     or multiple tags.
     Examples of this are "anonymous-address", "merchant-code-string"
     from the merchant-calculations-callback API
  */
  function get_arr_result($child_node) {
    $result = array();
    if(isset($child_node)) {
      if(is_associative_array($child_node)) {
        $result[] = $child_node;
      }
      else {
        foreach($child_node as $curr_node){
          $result[] = $curr_node;
        }
      }
    }
    return $result;
  }

  /* Returns true if a given variable represents an associative array */
  function is_associative_array( $var ) {
    return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
  }
?>
