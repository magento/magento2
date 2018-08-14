<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Landing block.
 */
class Landing extends Block
{
    /**
     * Link by text.
     *
     * @var string
     */
    protected $linkSelector = '//a[text()="%s"]';

    /**
     * 'Agree and Set up Magento' button.
     *
     * @var string
     */
    protected $agreeAndSetup = '.btn-prime.btn-submit';

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
    
    /**
     * Click on link.
     *
     * @param string $text
     * @return void
     */
    public function clickLink($text)
    {
        $this->_rootElement->find(sprintf($this->linkSelector, $text), Locator::SELECTOR_XPATH)->click();
    }
}
