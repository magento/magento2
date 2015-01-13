<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class GlobalSearch
 * Global Search fixture
 */
class GlobalSearch extends InjectableFixture
{
    protected $defaultDataSet = [
        'query' => 'catalogProductSimple::default::name',
    ];

    protected $query = [
        'attribute_code' => 'query',
        'backend_type' => 'virtual',
        'source' => 'Magento\Backend\Test\Fixture\GlobalSearch\Query',
    ];

    public function getQuery()
    {
        return $this->getData('query');
    }
}
