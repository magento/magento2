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

namespace Magento\CheckoutAgreements\Test\Block\Onepage;

use Mtf\Client\Element\Locator;
use Magento\Checkout\Test\Block\Onepage\Review;
use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;

/**
 * Class AgreementReview
 * One page checkout order review block
 */
class AgreementReview extends Review
{
    /**
     * Notification agreements locator
     *
     * @var string
     */
    protected $notification = 'div.mage-error';

    /**
     * Agreement locator
     *
     * @var string
     */
    protected $agreement = './/div[contains(@id, "checkout-review-submit")]//label[.="%s"]';

    /**
     * Agreement checkbox locator
     *
     * @var string
     */
    protected $agreementCheckbox = './/input[contains(@id, "agreement")]';

    /**
     * Get notification massage
     *
     * @return string
     */
    public function getNotificationMassage()
    {
        return $this->_rootElement->find($this->notification)->getText();
    }

    /**
     * Set agreement
     *
     * @param string $value
     * @return void
     */
    public function setAgreement($value)
    {
        $this->_rootElement->find($this->agreementCheckbox, Locator::SELECTOR_XPATH, 'checkbox')->setValue($value);
    }

    /**
     * Check agreement
     *
     * @param CheckoutAgreement $agreement
     * @return bool
     */
    public function checkAgreement(CheckoutAgreement $agreement)
    {
        return $this->_rootElement
            ->find(sprintf($this->agreement, $agreement->getCheckboxText()), Locator::SELECTOR_XPATH)->isVisible();
    }
}
