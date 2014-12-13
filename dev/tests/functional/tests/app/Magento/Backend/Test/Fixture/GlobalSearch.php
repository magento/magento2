<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
