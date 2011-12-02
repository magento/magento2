<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Model  for flex reports
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Reports_Model_Test extends Varien_Object
{

    public function getUsersCountries( )
    {
        return file_get_contents( Mage::getModuleDir('etc','Mage_Reports').DS.'flexTestDataCountries.xml' );
    }

    public function getUsersCities( $countryId )
    {
        $dom = new DOMDocument();
        $dom -> preserveWhiteSpace = false;
        $dom -> load( Mage::getModuleDir('etc','Mage_Reports').DS.'flexTestDataCities.xml' );

        $root = $dom -> documentElement;
        $rows = $root -> getElementsByTagName( 'row' );

        $childsToRemove = array();
        for( $i = 0; $i < $rows -> length; $i++)
        {
            for( $j = 0; $j < $rows -> item($i) -> childNodes -> length; $j ++ )
                if(
                    $rows -> item($i) -> childNodes -> item($j) -> nodeType == XML_ELEMENT_NODE
                        &&
                    $rows -> item($i) -> childNodes -> item($j) -> nodeName == 'countryId'
                        &&
                    $rows -> item($i) -> childNodes -> item($j) -> nodeValue != $countryId
                )
                    $childsToRemove[] = $rows -> item($i);
        }

        foreach( $childsToRemove as $child )
            $root -> removeChild( $child );

        return $dom -> saveXML();
    }

    public function getTimelineData( )
    {
        return file_get_contents( Mage::getModuleDir('etc','Mage_Reports').DS.'flexTestDataTimeline.xml' );
    }

    public function getAllLinearExample( )
    {
        $session = Mage::getModel('Mage_Reports_Model_Session');

        $startPoint = time() - 24*60*60;

        $allData = array();
        $countOfStartData = 12;
        for($i = 1; $i<= $countOfStartData; $i++)
        {
            $allData[] = array( 'time'=>date("Y-m-d H:i",$startPoint), 'value'=>rand(1, 100) );
            $startPoint += 30*60;
        }

        $allData[] = array( 'time'=>date("Y-m-d H:i",$startPoint+(90*60)));

        $session -> setData('startPoint', $startPoint);

        return $this -> returnAsDataSource( $allData );
    }

    public function getNewLinearData()
    {
        $session = Mage::getModel('Mage_Reports_Model_Session');


        $startPoint = $session -> getData('startPoint');

        $reset = 12;


        $newData  = array(
            array( 'time'=> date("Y-m-d H:i", $startPoint), 'value'=>rand(1, 100) )
        );

        $startPoint += 30*60;
        $newData[]  = array( 'time'=> date("Y-m-d H:i", $startPoint+(90*60)) );

        $session -> setData('startPoint', $startPoint);

        return $this -> returnAsDataSource( $newData, $reset );
    }

    private function returnAsDataSource( &$array , $reset = 0)
    {
        $dom = new DOMDocument();
        $dom -> preserveWhiteSpace = false;
        $dom -> loadXML( "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n<dataSource></dataSource>" );
        $root = $dom ->documentElement;
        if($reset)
        {
            $resetItem = $dom -> createElement("reset");
            $resetItem -> nodeValue = $reset;
            $root->appendChild($resetItem);
        }
        foreach($array  as $item )
        {
            $row = $dom->createElement('row');
            foreach( $item as $key => $val)
            {
                $valItem = $dom->createElement( $key );
                $valItem->nodeValue = $val;
                $row->appendChild($valItem);
            }

            $root->appendChild($row);
        }

        return $dom->saveXML();
    }
}
