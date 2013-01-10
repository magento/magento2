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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter templates grid block action item renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Newsletter_Template_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Renderer for "Action" column in Newsletter templates grid
     *
     * @var Mage_Newsletter_Model_Template $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if($row->isValidForSend()) {
            $actions[] = array(
                'url' => $this->getUrl('*/newsletter_queue/edit', array('template_id' => $row->getId())),
                'caption' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Queue Newsletter...')
            );
        }

        $actions[] = array(
            'url'     => $this->getUrl('*/*/preview', array('id'=>$row->getId())),
            'popup'   => true,
            'caption' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Preview')
        );

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
