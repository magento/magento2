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
 * XmlConnect Adminhtml AirMail template preview block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Template_Preview extends Mage_Adminhtml_Block_Widget
{
    /**
     * Retrieve processed template
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ((int)$this->getRequest()->getParam('queue_preview')) {
            $id = $this->getRequest()->getParam('queue_preview');
            /** @var $template Mage_XmlConnect_Model_Queue */
            $template = Mage::getModel('Mage_XmlConnect_Model_Queue');
        } else {
            $id = (int)$this->getRequest()->getParam('id');
            /** @var $template Mage_XmlConnect_Model_Template */
            $template = Mage::getModel('Mage_XmlConnect_Model_Template');
        }

        if ($id) {
            $template->load($id);
        }

        $storeId = (int)$this->getRequest()->getParam('store_id');

        if (!$storeId) {
            $storeId = Mage::app()->getDefaultStoreView()->getId();
        }

        $template->emulateDesign($storeId);
        $templateProcessed = $template->getProcessedTemplate(array(), true);
        $template->revertDesign();

        return $templateProcessed;
    }
}
