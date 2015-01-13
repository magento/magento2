<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class UrlRewrite
 * Data for creation url rewrite
 */
class UrlRewrite extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'request_path' => 'test-test-test%isolation%.html',
            'target_path' => 'http://www.ebayinc.com/',
            'redirect_type' => 'Temporary (302)',
            'store_id' => 'Main Website/Main Website Store/Default Store View',
        ];

        $this->_data['default_without_target'] = [
            'request_path' => 'test-test-test%isolation%.html',
            'redirect_type' => 'Temporary (302)',
            'store_id' => 'Main Website/Main Website Store/Default Store View',
        ];

        $this->_data['custom_rewrite_wishlist'] = [
            'store_id' => 'Main Website/Main Website Store/Default Store View',
            'request_path' => 'wishlist/%isolation%',
            'target_path' => 'http://google.com',
            'redirect_type' => 'Temporary (302)',
            'description' => 'test description',
        ];
    }
}
