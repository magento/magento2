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
 * Adminhtml airmail queue grid block action item renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Queue_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Render grid row
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = array(
            array(
                'caption'   => $this->__('Preview'),
                'url'       => $this->getUrl('*/*/previewQueue', array('id' => $row->getId())),
                'popup'     => true,
            ),
        );

        if ($row->getStatus() == Mage_XmlConnect_Model_Queue::STATUS_IN_QUEUE) {
            $actions[] = array(
                'caption'   => $this->__('Edit'),
                'url'       => $this->getUrl('*/*/editQueue', array('id' => $row->getId())),
            );
            $actions[] = array(
                'caption'   => $this->__('Cancel'),
                'url'       => $this->getUrl('*/*/cancelQueue', array('id' => $row->getId())),
                'confirm'   => $this->__('Are you sure you whant to cancel a message?')
            );
        }

        $actions[] = array(
            'caption'   => $this->__('Delete'),
            'url'       => $this->getUrl('*/*/deleteQueue', array('id' => $row->getId())),
            'confirm'   => $this->__('Are you sure you whant to delete a message?')
        );

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
