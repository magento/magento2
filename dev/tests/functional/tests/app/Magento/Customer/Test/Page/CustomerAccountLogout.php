<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Page;

use Mtf\Page\Page;

/**
 * Class CustomerAccountLogout
 * Customer frontend logout page.
 *
 */
class CustomerAccountLogout extends Page
{
    /**
     * URL for customer logout
     */
    const MCA = 'customer/account/logout';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }
}
