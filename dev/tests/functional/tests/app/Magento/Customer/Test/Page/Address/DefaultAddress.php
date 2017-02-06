<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Page\Address;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Default address page.
 */
class DefaultAddress extends Page
{
    /**
     * URL for customer Dashboard.
     */
    const MCA = 'customer/address/index';

    /**
     * Selector for default address block.
     *
     * @var string
     */
    protected $defaultAddressesSelector = '.block-addresses-default';

    /**
     * Get default addresses block.
     *
     * @return \Magento\Customer\Test\Block\Account\AddressesDefault
     */
    public function getDefaultAddresses()
    {
        return Factory::getBlockFactory()->getMagentoCustomerAccountAddressesDefault(
            $this->browser->find($this->defaultAddressesSelector, Locator::SELECTOR_CSS)
        );
    }
}
