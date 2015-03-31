<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * class MultishippingCheckoutRegister
 * Register new customer while performing multishipping addresses checkout
 *
 */
class MultishippingCheckoutRegister extends Page
{
    /**
     * URL for register customer page
     */
    const MCA = 'multishipping/checkout/register';

    /**
     * Customer register block form
     *
     * @var string
     */
    protected $registerBlock = '#form-validate';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get customer register block form
     *
     * @return \Magento\Customer\Test\Block\Form\Register
     */
    public function getRegisterBlock()
    {
        return Factory::getBlockFactory()->getMagentoCustomerFormRegister(
            $this->_browser->find($this->registerBlock, Locator::SELECTOR_CSS)
        );
    }
}
