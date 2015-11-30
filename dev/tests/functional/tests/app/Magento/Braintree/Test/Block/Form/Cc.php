<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Block\Form;


use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Payment\Test\Block\Form\Cc as CreditCard;

/**
 * Class Cc
 * Form for filling credit card data for Braintree payment method
 */
class Cc extends CreditCard
{
    /**
     * Braintree iFrame locator
     *
     * @var array
     */
    protected $braintreeForm = [
        "credit_card_number" => "#braintree-hosted-field-number",
        "expiration" => "#braintree-hosted-field-expirationDate",
        "cvv" => "#braintree-hosted-field-cvv",
    ];

    /**
     * Cc constructor.
     * @param SimpleElement $element
     * @param BlockFactory $blockFactory
     * @param Mapper $mapper
     * @param BrowserInterface $browser
     */
    public function __construct(
        SimpleElement $element,
        BlockFactory $blockFactory,
        Mapper $mapper,
        BrowserInterface $browser
    ) {
        parent::__construct($element, $blockFactory, $mapper, $browser);
    }

    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $mapping = $this->dataMapping($fixture->getData());
        foreach ($this->braintreeForm as $field => $iframe) {
            $this->browser->switchToFrame(new Locator($iframe));
            $element = $this->browser->find('body');
            $this->_fill([$mapping[$field]], $element);
            $this->browser->switchToFrame();
        }
    }
}
