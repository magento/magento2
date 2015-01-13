<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Page\Address;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

/**
 * Class DefaultAddress
 * Default address page
 */
class DefaultAddress extends Page
{
    /**
     * URL for customer Dashboard
     */
    const MCA = 'customer/address/index';

    /**
     * Selector for default address block
     *
     * @var string
     */
    protected $defaultAddressesSelector = '.block-addresses-default .box-address-billing';

    /**
     * Get default addresses block
     *
     * @return \Magento\Customer\Test\Block\Account\AddressesDefault
     */
    public function getDefaultAddresses()
    {
        return Factory::getBlockFactory()->getMagentoCustomerAccountAddressesDefault(
            $this->_browser->find($this->defaultAddressesSelector, Locator::SELECTOR_CSS)
        );
    }
}
