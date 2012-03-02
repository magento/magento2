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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product price xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Price extends Mage_XmlConnect_Block_Catalog
{
    /**
     * Default product price renderer block factory name
     *
     * @var string
     */
    protected $_defaultPriceRenderer = 'Mage_XmlConnect_Block_Catalog_Product_Price_Default';

    /**
     * Store supported product price xml renderers based on product types
     *
     * @var array
     */
    protected $_renderers = array();

    /**
     * Store already initialized renderers instances
     *
     * @var array
     */
    protected $_renderersInstances = array();

    /**
     * Add new product price renderer
     *
     * @param string $type
     * @param string $renderer
     * @return Mage_XmlConnect_Block_Product_Options
     */
    public function addRenderer($type, $renderer)
    {
        if (!isset($this->_renderers[$type])) {
            $this->_renderers[$type] = $renderer;
        }
        return $this;
    }

    /**
     * Collect product prices to current xml object
     */
    public function collectProductPrices()
    {
        $product = $this->getProduct();
        $xmlObject = $this->getProductXmlObj();

        if ($product && $product->getId()) {
            $type = $product->getTypeId();
            if (isset($this->_renderers[$type])) {
                $blockName = $this->_renderers[$type];
            } else {
                $blockName = $this->_defaultPriceRenderer;
            }

            $renderer = $this->getLayout()->getBlock($blockName);
            if (!$renderer) {
                $renderer = $this->getLayout()->createBlock($blockName);
            }

            if ($renderer) {
                $renderer->collectProductPrices($product, $xmlObject);
            }
        }
    }
}
