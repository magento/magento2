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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mage_Backend accordion widget
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Widget_Accordion extends Mage_Backend_Block_Widget
{
    protected $_items = array();

    /**
     * @var string
     */
    protected $_template = 'Mage_Backend::widget/accordion.phtml';

    public function getItems()
    {
        return $this->_items;
    }
    
    public function addItem($itemId, $config)
    {
        $this->_items[$itemId] = $this->getLayout()
            ->createBlock(
                'Mage_Backend_Block_Widget_Accordion_Item',
                $this->getNameInLayout() . '-' . $itemId
            )
            ->setData($config)
            ->setAccordion($this)
            ->setId($itemId);
        if (isset($config['content']) && $config['content'] instanceof Mage_Core_Block_Abstract) {
            $this->_items[$itemId]->setChild($itemId.'_content', $config['content']);
        }
            
        $this->setChild($itemId, $this->_items[$itemId]);
        return $this;
    }
}
