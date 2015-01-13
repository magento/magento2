<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Info
 * Order information tab block
 *
 */
class Info extends Block
{
    /**
     * 3D Secure Verification Result
     *
     * @var string
     */
    protected $_verificationResult = '//tr[normalize-space(th)="3D Secure Verification Result:"]/td';

    /**
     * 3D Secure Cardholder Validation
     *
     * @var string
     */
    protected $_cardholderValidation = '//tr[normalize-space(th)="3D Secure Cardholder Validation:"]/td';

    /**
     * 3D Secure Electronic Commerce Indicator
     *
     * @var string
     */
    protected $_eCommerceIndicator = '//tr[normalize-space(th)="3D Secure Electronic Commerce Indicator:"]/td';

    /**
     * Order status selector
     *
     * @var string
     */
    protected $orderStatus = '#order_status';

    /**
     * Get 3D Secure Verification Result
     *
     * @return array|string
     */
    public function getVerificationResult()
    {
        return $this->_rootElement->find($this->_verificationResult, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get 3D Secure Verification Result
     *
     * @return array|string
     */
    public function getCardholderValidation()
    {
        return $this->_rootElement->find($this->_cardholderValidation, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get 3D Secure Electronic Commerce Indicator
     *
     * @return array|string
     */
    public function getEcommerceIndicator()
    {
        return $this->_rootElement->find($this->_eCommerceIndicator, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get order status from info block
     *
     * @return array|string
     */
    public function getOrderStatus()
    {
        return $this->_rootElement->find($this->orderStatus)->getText();
    }
}
