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
namespace Magento\Catalog\Block\Product;

/**
 * Product send to friend block
 */
class Send extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Customer view helper
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerView;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\View $customerView
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\View $customerView,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerView = $customerView;
        parent::__construct(
            $context,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->_customerView->getCustomerName($this->_customerSession->getCustomerDataObject());
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return (string)$this->_customerSession->getCustomerDataObject()->getEmail();
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }
}
