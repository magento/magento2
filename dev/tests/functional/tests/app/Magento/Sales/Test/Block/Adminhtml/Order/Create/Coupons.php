<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Block\Form;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;

/**
 * Adminhtml sales order create coupons block.
 */
class Coupons extends Form
{
    /**
     * Fill discount code input selector.
     *
     * @var string
     */
    protected $couponCode = 'input[name="coupon_code"]';

    /**
     * Click apply button selector.
     *
     * @var string
     */
    protected $applyButton = './/*[@id="coupons:code"]/following-sibling::button[contains(@onclick,"coupons:code")]';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Enter discount code and click apply button.
     *
     * @param SalesRule $code
     * @return void
     */
    public function applyCouponCode(SalesRule $code)
    {
        $couponField = $this->_rootElement->find($this->couponCode);
        $couponField->click();
        $couponField->keys(str_split($code->getCouponCode()));
        $this->waitForElementVisible($this->applyButton, Locator::SELECTOR_XPATH);
        $this->_rootElement->find($this->applyButton, Locator::SELECTOR_XPATH)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }
}
