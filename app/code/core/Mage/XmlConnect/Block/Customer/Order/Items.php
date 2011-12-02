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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer order view items xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Customer_Order_Items extends Mage_Sales_Block_Order_Items
{
    /**
     * Initialize default item renderer
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addItemRender('default', 'Mage_XmlConnect_Block_Customer_Order_Item_Renderer_Default', null);
    }

    /**
     * Retrieve item renderer block
     *
     * @param string $type
     * @return Mage_Core_Block_Abstract
     */
    public function getItemRenderer($type)
    {
        if (empty($type) || !isset($this->_itemRenders[$type])) {
            $type = 'default';
        }

        if (is_null($this->_itemRenders[$type]['renderer'])) {
            $this->_itemRenders[$type]['renderer'] = $this->getLayout()
                ->createBlock($this->_itemRenders[$type]['block'])->setRenderedBlock($this);
        }
        return $this->_itemRenders[$type]['renderer'];
    }

    /**
     * Render XML for items
     * (get from template: order/items.phtml)
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $orderXmlObj
     * @return null
     */
    public function addItemsToXmlObject(Mage_XmlConnect_Model_Simplexml_Element $orderXmlObj)
    {
        $itemsXml = $orderXmlObj->addChild('ordered_items');

        foreach ($this->getItems() as $item) {
            if ($item->getParentItem()) {
                // if Item is option of grouped product - do not render it
                continue;
            }
            $type = $this->_getItemType($item);

            // TODO: take out all Enterprise renderers from layout update into array an realize checking of their using
            // Check if the Enterprise_GiftCard module is available for rendering
            if ($type == 'giftcard' && !is_object(Mage::getConfig()->getNode('modules/Enterprise_GiftCard'))) {
                continue;
            }
            $renderer = $this->getItemRenderer($type)->setItem($item);
            if (method_exists($renderer, 'addItemToXmlObject')) {
                $renderer->addItemToXmlObject($itemsXml);
            }
        }
    }
}
