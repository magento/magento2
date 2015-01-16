<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class TaxClass
 */
class TaxClass extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Tax\Test\Repository\TaxClass';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Tax\Test\Handler\TaxClass\TaxClassInterface';

    protected $defaultDataSet = [
        'class_name' => 'Tax Class %isolation%',
    ];

    protected $class_id = [
        'attribute_code' => 'class_id',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $class_name = [
        'attribute_code' => 'class_name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $class_type = [
        'attribute_code' => 'class_type',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'CUSTOMER',
        'input' => '',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'virtual',
    ];

    public function getClassId()
    {
        return $this->getData('class_id');
    }

    public function getClassName()
    {
        return $this->getData('class_name');
    }

    public function getClassType()
    {
        return $this->getData('class_type');
    }

    public function getId()
    {
        return $this->getData('id');
    }
}
