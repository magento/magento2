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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\System\Store\Delete;

/**
 * Adminhtml store delete group block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Backend\Block\Template
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('website_id');

        $this->setTemplate('system/store/delete_website.phtml');
        $this->setAction($this->getUrl('adminhtml/*/deleteWebsitePost', array('website_id' => $itemId)));
        $this->addChild(
            'confirm_deletion_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Delete Web Site'), 'onclick' => "deleteForm.submit()", 'class' => 'cancel')
        );
        $onClick = "setLocation('" . $this->getUrl('adminhtml/*/editWebsite', array('website_id' => $itemId)) . "')";
        $this->addChild(
            'cancel_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Cancel'), 'onclick' => $onClick, 'class' => 'cancel')
        );
        $this->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Back'), 'onclick' => $onClick, 'class' => 'cancel')
        );
        return parent::_prepareLayout();
    }
}
