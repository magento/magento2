<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
