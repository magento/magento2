<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Page;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

/**
 * Customer Address Edit page.
 *
 */
class CustomerAddressEdit extends Page
{
    /**
     * URL for Customer Address Edit page
     */
    const MCA = 'customer/address/edit';

    /**
     * Customer Address Edit form
     *
     * @var string
     */
    protected $editForm = '#form-validate';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get Customer Address Edit form
     *
     * @return \Magento\Customer\Test\Block\Address\Edit
     */
    public function getEditForm()
    {
        return Factory::getBlockFactory()->getMagentoCustomerAddressEdit(
            $this->_browser->find($this->editForm, Locator::SELECTOR_CSS)
        );
    }
}
