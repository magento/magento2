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
 * Tab design accordion xml renderer
 *
 * @category     Mage
 * @package      Mage_Xmlconnect
 * @author       Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Design_Accordion
    extends Mage_Adminhtml_Block_Widget_Accordion
{
    /**
     * Add accordion item by specified block
     *
     * @param string $itemId
     * @param mixed $block
     */
    public function addAccordionItem($itemId, $block)
    {
        if (strpos($block, '/') !== false) {
            $block = $this->getLayout()->createBlock($block);
        } else {
            $block = $this->getLayout()->getBlock($block);
        }

        $this->addItem($itemId, array(
            'title'   => $block->getTitle(),
            'content' => $block->toHtml(),
            'open'    => $block->getIsOpen(),
        ));
    }
}
