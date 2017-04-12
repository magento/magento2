<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Page;

use Magento\Mtf\Page\Page;

/**
 * Customer frontend logout page.
 */
class CustomerAccountLogout extends Page
{
    /**
     * URL for customer logout.
     */
    const MCA = 'customer/account/logout';

    /**
     * Init page. Set page url.
     *
     * @return void
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_frontend_url'] . self::MCA;
    }
}
