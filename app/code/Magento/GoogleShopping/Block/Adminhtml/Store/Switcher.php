<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Block\Adminhtml\Store;

/**
 * Adminhtml GoogleShopping Store Switcher
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Switcher extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * Whether the switcher should show default option
     *
     * @var bool
     */
    protected $_hasDefaultOption = false;

    /**
     * Set overridden params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseConfirm(false)->setSwitchUrl($this->getUrl('adminhtml/*/*', ['store' => null]));
    }
}
