<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Landing block.
 */
class Landing extends Block
{
    /**
     * 'Agree and Set up Magento' button.
     *
     * @var string
     */
    protected $agreeAndSetup = '.btn-lg.btn-primary';

    /**
     * 'Terms & Agreement' link.
     *
     * @var string
     */
    protected $termsAndAgreement = "[ng-click*='previous']";

    /**
     * Click on 'Agree and Set up Magento' button.
     *
     * @return void
     */
    public function clickAgreeAndSetup()
    {
        $this->_rootElement->find($this->agreeAndSetup, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click on 'Terms & Agreement' link.
     *
     * @return void
     */
    public function clickTermsAndAgreement()
    {
        $this->_rootElement->find($this->termsAndAgreement, Locator::SELECTOR_CSS)->click();
    }
}
