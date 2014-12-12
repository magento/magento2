<?php
/**
 * @spi
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Handler\Ui;

use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;

/**
 * UI handler for creating customer address.
 */
class CreateAddress extends \Mtf\Handler\Ui
{
    /**
     * Execute handler
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var \Magento\Customer\Test\Fixture\Address $fixture */
        // Pages
        $loginPage = Factory::getPageFactory()->getCustomerAccountLogin();
        $addressPage = Factory::getPageFactory()->getCustomerAddressEdit();

        $loginPage->open();
        if ($loginPage->getLoginBlock()->isVisible()) {
            $loginPage->getLoginBlock()->login($fixture->getCustomer());
        }

        $addressPage->open();
        $addressPage->getEditForm()->editCustomerAddress($fixture);
    }
}
