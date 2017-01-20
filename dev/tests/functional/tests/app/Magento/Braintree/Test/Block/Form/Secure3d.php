<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Block\Form;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Block\Form;

/**
 * Form for filling 3D Secure password for Braintree payment method
 */
class Secure3d extends Form
{
    /**
     * 3D Secure iFrame locator.
     *
     * @var array
     */
    protected $braintree3dSecure = "//iframe[contains(@src, 'braintreegateway.com/3ds')]";

    /**
     * Submit button button css selector.
     *
     * @var string
     */
    protected $submitButton = 'input[name="UsernamePasswordEntry"]';

    /**
     * Switch to 3D Secure iFrame.
     *
     * @param array $locator
     */
    public function switchToFrame($locator)
    {
         $this->browser->switchToFrame(new Locator($locator, Locator::SELECTOR_XPATH));
         $this->browser->switchToFrame(new Locator($locator, Locator::SELECTOR_XPATH));
    }

    /**
     * Click Submit button.
     *
     * @return void
     */
    public function submit()
    {
        $this->browser->find($this->submitButton)->click();
    }

    /**
     * Fill the 3D Secure form and submit it.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this|void
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $mapping = $this->dataMapping($fixture->getData());
        $this->switchToFrame($this->braintree3dSecure);
        $element = $this->browser->find('body');
        $this->_fill([$mapping['secure3d_password']], $element);
        $this->submit();
        $this->browser->switchToFrame();
    }
}
