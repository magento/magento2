<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ImportExport\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class ImportExport
 * Export fixture
 */
class ImportExport extends InjectableFixture
{
    protected $defaultDataSet = [
        'entity' => 'Products',
        'behavior' => 'CSV',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $entity = [
        'attribute_code' => 'entity',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $behavior = [
        'attribute_code' => 'behavior',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'append',
        'input' => '',
    ];

    protected $data_export = [
        'attribute_code' => 'data',
        'backend_type' => 'longtext',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    public function getId()
    {
        return $this->getData('id');
    }

    public function getEntity()
    {
        return $this->getData('entity');
    }

    public function getBehavior()
    {
        return $this->getData('behavior');
    }

    public function getDataExport()
    {
        return $this->getData('data_export');
    }
}
