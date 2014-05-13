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
        $this->setUseConfirm(false)->setSwitchUrl($this->getUrl('adminhtml/*/*', array('store' => null)));
    }
}
