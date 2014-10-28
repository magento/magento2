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
