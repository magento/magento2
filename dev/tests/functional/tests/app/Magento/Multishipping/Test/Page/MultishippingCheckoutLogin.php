<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Multishipping login page.
 */
class MultishippingCheckoutLogin extends Page
{
    /**
     * URL for multishipping login page.
     */
    const MCA = 'multishipping/checkout/login';

    /**
     * Form for customer login.
     *
     * @var string
     */
    protected $loginBlock = '.login-container';

    /**
     * Init page. Set page url.
     *
     * @return void
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get form for customer login.
     *
     * @return \Magento\Customer\Test\Block\Form\Login
     */
    public function getLoginBlock()
    {
        return Factory::getBlockFactory()->getMagentoCustomerFormLogin(
            $this->browser->find($this->loginBlock, Locator::SELECTOR_CSS)
        );
    }
}
