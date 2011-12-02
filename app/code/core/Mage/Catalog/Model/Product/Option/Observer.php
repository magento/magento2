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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Custom Options Observer
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Option_Observer
{
    /**
     * Copy quote custom option files to order custom option files
     *
     * @param Varien_Object $observer
     * @return Mage_Catalog_Model_Product_Option_Observer
     */
    public function copyQuoteFilesToOrderFiles($observer)
    {
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $observer->getEvent()->getItem();

        if (is_array($quoteItem->getOptions())) {
            foreach ($quoteItem->getOptions() as $itemOption) {
                $code = explode('_', $itemOption->getCode());
                if (isset($code[1]) && is_numeric($code[1]) && ($option = $quoteItem->getProduct()->getOptionById($code[1]))) {
                    if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE) {
                        /* @var $_option Mage_Catalog_Model_Product_Option */
                        try {
                            $group = $option->groupFactory($option->getType())
                                ->setQuoteItemOption($itemOption)
                                ->copyQuoteToOrder();

                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
        return $this;
    }
}
