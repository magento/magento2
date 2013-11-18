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
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout select billing address
 *
 * @category   Magento
 * @package    Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Block\Multishipping\Address;

class Select extends \Magento\Checkout\Block\Multishipping\AbstractMultishipping
{
    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(__('Change Billing Address') . ' - ' . $headBlock->getDefaultTitle());
        }
        return parent::_prepareLayout();
    }

    public function getAddressCollection()
    {
        $collection = $this->getData('address_collection');
        if (is_null($collection)) {
            $collection = $this->_multishipping->getCustomer()->getAddresses();
            $this->setData('address_collection', $collection);
        }
        return $collection;
    }
    
    public function isAddressDefaultBilling($address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultBilling();
    }
    
    public function isAddressDefaultShipping($address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultShipping();
    }
    
    public function getEditAddressUrl($address)
    {
        return $this->getUrl('*/*/editAddress', array('id'=>$address->getId()));
    }

    public function getSetAddressUrl($address)
    {
        return $this->getUrl('*/*/setBilling', array('id'=>$address->getId()));
    }

    public function getAddNewUrl()
    {
        return $this->getUrl('*/*/newBilling');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/multishipping/billing');
    }
}
