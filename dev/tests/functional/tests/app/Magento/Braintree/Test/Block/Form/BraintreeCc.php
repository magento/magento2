<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Block\Form;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Payment\Test\Block\Form\PaymentCc as CreditCard;

/**
 * Form for filling credit card data for Braintree payment method.
 */
class BraintreeCc extends CreditCard
{
    /**
     * Braintree iFrame locator.
     *
     * @var array
     */
    protected $braintreeForm = [
        "cc_number" => "#braintree-hosted-field-number",
        "cc_exp_month" => "#braintree-hosted-field-expirationMonth",
        "cc_exp_year" => "#braintree-hosted-field-expirationYear",
        "cc_cid" => "#braintree-hosted-field-cvv",
    ];

    /**
     * Fill Braintree credit card form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return void
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        ObjectManager::getInstance()->create(Locator::class, []);
        $mapping = $this->dataMapping($fixture->getData());
        foreach ($this->braintreeForm as $field => $iframe) {
            $element = $this->browser->find('body');
            $this->browser->waitUntil(
                function () use ($element, $iframe) {
                    $fieldElement = $element->find($iframe);
                    return $fieldElement->isVisible() ? true : null;
                }
            );
            $this->browser->switchToFrame(new Locator($iframe));
            $element = $this->browser->find('body');
            $this->browser->waitUntil(
                function () use ($element) {
                    $fieldElement = $element->find('input');
                    return $fieldElement->isVisible() ? true : null;
                }
            );
            $this->_fill([$mapping[$field]], $element);
            $this->browser->switchToFrame();
        }
    }
}
