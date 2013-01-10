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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Catalog_Special extends Mage_Rss_Block_Catalog_Abstract
{
    /**
     * Zend_Date object for date comparsions
     *
     * @var Zend_Date $_currentDate
     */
    protected static $_currentDate = null;

    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_catalog_special_'.$this->_getStoreId().'_'.$this->_getCustomerGroupId());
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
         //store id is store view id
        $storeId = $this->_getStoreId();
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

        //customer group id
        $customerGroupId = $this->_getCustomerGroupId();

        $product = Mage::getModel('Mage_Catalog_Model_Product');

        $fields = array(
            'final_price',
            'price'
        );
        $specials = $product->setStoreId($storeId)->getResourceCollection()
            ->addPriceDataFieldFilter('%s < %s', $fields)
            ->addPriceData($customerGroupId, $websiteId)
            ->addAttributeToSelect(
                    array(
                        'name', 'short_description', 'description', 'price', 'thumbnail',
                        'special_price', 'special_to_date',
                        'msrp_enabled', 'msrp_display_actual_price_type', 'msrp'
                    ),
                    'left'
            )
            ->addAttributeToSort('name', 'asc')
        ;

        $newurl = Mage::getUrl('rss/catalog/special/store_id/' . $storeId);
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('%s - Special Products', Mage::app()->getStore()->getFrontendName());
        $lang = Mage::getStoreConfig('general/locale/code');

        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');
        $data = array('title' => $title,
                'description' => $title,
                'link'        => $newurl,
                'charset'     => 'UTF-8',
                'language'    => $lang
                );
        $rssObj->_addHeader($data);

        $results = array();
        /*
        using resource iterator to load the data one by one
        instead of loading all at the same time. loading all data at the same time can cause the big memory allocation.
        */
        Mage::getSingleton('Mage_Core_Model_Resource_Iterator')->walk(
            $specials->getSelect(),
            array(array($this, 'addSpecialXmlCallback')),
            array('rssObj'=> $rssObj, 'results'=> &$results)
        );

        if (sizeof($results)>0) {
            foreach($results as $result){
                // render a row for RSS feed
                $product->setData($result);
                $html = sprintf('<table><tr>
                    <td><a href="%s"><img src="%s" alt="" border="0" align="left" height="75" width="75" /></a></td>
                    <td style="text-decoration:none;">%s',
                    $product->getProductUrl(),
                    $this->helper('Mage_Catalog_Helper_Image')->init($product, 'thumbnail')->resize(75, 75),
                    $this->helper('Mage_Catalog_Helper_Output')->productAttribute(
                        $product,
                        $product->getDescription(),
                        'description'
                    )
                );

                // add price data if needed
                if ($product->getAllowedPriceInRss()) {
                    if (Mage::helper('Mage_Catalog_Helper_Data')->canApplyMsrp($product)) {
                        $html .= '<br/><a href="' . $product->getProductUrl() . '">'
                            . $this->__('Click for price') . '</a>';
                    } else {
                        $special = '';
                        if ($result['use_special']) {
                            $special = '<br />' . Mage::helper('Mage_Catalog_Helper_Data')->__('Special Expires On: %s', $this->formatDate($result['special_to_date'], Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
                        }
                        $html .= sprintf('<p>%s %s%s</p>',
                            Mage::helper('Mage_Catalog_Helper_Data')->__('Price: %s', Mage::helper('Mage_Core_Helper_Data')->currency($result['price'])),
                            Mage::helper('Mage_Catalog_Helper_Data')->__('Special Price: %s', Mage::helper('Mage_Core_Helper_Data')->currency($result['final_price'])),
                            $special
                        );
                    }
                }

                $html .= '</td></tr></table>';

                $rssObj->_addEntry(array(
                    'title'       => $product->getName(),
                    'link'        => $product->getProductUrl(),
                    'description' => $html
                ));
            }
        }
        return $rssObj->createRssXml();
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     */
    public function addSpecialXmlCallback($args)
    {
        if (!isset(self::$_currentDate)) {
            self::$_currentDate = new Zend_Date();
        }

        // dispatch event to determine whether the product will eventually get to the result
        $product = new Varien_Object(array('allowed_in_rss' => true, 'allowed_price_in_rss' => true));
        $args['product'] = $product;
        Mage::dispatchEvent('rss_catalog_special_xml_callback', $args);
        if (!$product->getAllowedInRss()) {
            return;
        }

        // add row to result and determine whether special price is active (less or equal to the final price)
        $row = $args['row'];
        $row['use_special'] = false;
        $row['allowed_price_in_rss'] = $product->getAllowedPriceInRss();
        if (isset($row['special_to_date']) && $row['final_price'] <= $row['special_price']
            && $row['allowed_price_in_rss']
        ) {
            $compareDate = self::$_currentDate->compareDate($row['special_to_date'], Varien_Date::DATE_INTERNAL_FORMAT);
            if (-1 === $compareDate || 0 === $compareDate) {
                $row['use_special'] = true;
            }
        }

       $args['results'][] = $row;
    }


    /**
     * Function for comparing two items in collection
     *
     * @param   Varien_Object $item1
     * @param   Varien_Object $item2
     * @return  boolean
     */
    public function sortByStartDate($a, $b)
    {
        return $a['start_date']>$b['start_date'] ? -1 : ($a['start_date']<$b['start_date'] ? 1 : 0);
    }
}
