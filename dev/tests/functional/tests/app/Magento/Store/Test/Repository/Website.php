<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Website
 * Data for creation Website
 */
class Website extends AbstractRepository
{
    /**
     * @constructor
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['all_websites'] = [
            'name' => 'All Websites',
            'website_id' => 0,
        ];

        $this->_data['main_website'] = [
            'name' => 'Main Website',
            'code' => 'base',
            'sort_order' => 0,
            'website_id' => 1,
        ];

        $this->_data['custom_website'] = [
            'name' => 'Web_Site_%isolation%',
            'code' => 'code_%isolation%',
        ];
    }
}
