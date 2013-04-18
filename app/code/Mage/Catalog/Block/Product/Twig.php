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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Block_Product_Twig extends Mage_Core_Block_Template
{
    public function __construct(Mage_Core_Block_Template_Context $context,
            Magento_Datasource_Factory $datasourceFactory,
            array $data = array())
    {
        parent::__construct($context, $data);
        $this->addAdjustableRenderer('price', 'tier', 'simple',
            'Mage_Catalog_Block_Product_Price', 'product/view/tierprices.phtml');
        $this->addAdjustableRenderer('price', 'price', 'simple',
            'Mage_Catalog_Block_Product_Price', 'product/price.phtml');
    }

    /**
     * For backward compatibility with existing method
     *
     * @param string $type
     * @param string $block
     * @param string $template
     */
    public function addPriceBlockType($type, $block = '', $template = '')
    {
        $this->addAdjustableRenderer('price', 'price', $type, $block, $template);
    }

    /**
     * For backward compatibility with existing method
     *
     * @param $template
     */
    public function setTierPriceTemplate($template)
    {
        $this->addAdjustableRenderer('price', 'tier', 'simple', 'Mage_Catalog_Block_Product_Price', $template);
    }
}