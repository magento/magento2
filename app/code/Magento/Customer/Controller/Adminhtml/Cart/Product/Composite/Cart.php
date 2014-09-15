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
namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite;

use Magento\Framework\Model\Exception;

/**
 * Catalog composite product configuration controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cart extends \Magento\Backend\App\Action
{
    /**
     * Customer we're working with
     *
     * @var int id of the customer
     */
    protected $_customerId;

    /**
     * Quote we're working with
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote = null;

    /**
     * Quote item we're working with
     *
     * @var \Magento\Sales\Model\Quote\Item
     */
    protected $_quoteItem = null;

    /**
     * Loads customer, quote and quote item by request params
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initData()
    {
        $this->_customerId = (int)$this->getRequest()->getParam('customer_id');
        if (!$this->_customerId) {
            throw new \Magento\Framework\Model\Exception(__('No customer ID defined.'));
        }

        $quoteItemId = (int)$this->getRequest()->getParam('id');
        $websiteId = (int)$this->getRequest()->getParam('website_id');

        $this->_quote = $this->_objectManager->create(
            'Magento\Sales\Model\Quote'
        )->setWebsite(
            $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getWebsite($websiteId)
        )->loadByCustomer(
            $this->_customerId
        );

        $this->_quoteItem = $this->_quote->getItemById($quoteItemId);
        if (!$this->_quoteItem) {
            throw new Exception(__('Please correct the quote items and try again.'));
        }

        return $this;
    }

    /**
     * Check the permission to Manage Customers
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Customer::manage');
    }
}
