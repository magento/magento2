<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Page;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

/**
 */
class CustomerAccountForgotPassword extends Page
{
    /**
     * URL for reset customer password
     */
    const MCA = 'customer/account/forgotpassword';

    /**
     * @var string
     */
    protected $forgotPasswordForm = '#form-validate';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get Customer Forgot Password form
     *
     * @return \Magento\Customer\Test\Block\Form\ForgotPassword
     */
    public function getForgotPasswordForm()
    {
        return Factory::getBlockFactory()->getMagentoCustomerFormForgotPassword(
            $this->_browser->find(
                $this->forgotPasswordForm,
                Locator::SELECTOR_CSS
            )
        );
    }
}
