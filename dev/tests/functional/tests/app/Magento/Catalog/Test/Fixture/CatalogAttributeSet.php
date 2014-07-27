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

namespace Magento\Catalog\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CatalogAttributeSet
 * Catalog Attribute Set fixture
 */
class CatalogAttributeSet extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Catalog\Test\Repository\CatalogAttributeSet';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Catalog\Test\Handler\CatalogAttributeSet\CatalogAttributeSetInterface';

    protected $defaultDataSet = [
        'attribute_set_name' => 'Default_attribute_set_%isolation%',
        'skeleton_set' => ['dataSet' => 'default'],
    ];

    protected $attribute_set_id = [
        'attribute_code' => 'attribute_set_id',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $entity_type_id = [
        'attribute_code' => 'entity_type_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $attribute_set_name = [
        'attribute_code' => 'attribute_set_name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sort_order = [
        'attribute_code' => 'sort_order',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $skeleton_set = [
        'attribute_code' => 'skeleton_set',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogAttributeSet\SkeletonSet',
    ];

    protected $assigned_attributes = [
        'attribute_code' => 'assigned_attributes',
        'backend_type' => 'virtual',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogAttributeSet\AssignedAttributes',
    ];

    protected $group = [
        'attribute_code' => 'group',
        'backend_type' => 'virtual',
    ];

    public function getAttributeSetId()
    {
        return $this->getData('attribute_set_id');
    }

    public function getEntityTypeId()
    {
        return $this->getData('entity_type_id');
    }

    public function getAttributeSetName()
    {
        return $this->getData('attribute_set_name');
    }

    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    public function getSkeletonSet()
    {
        return $this->getData('skeleton_set');
    }

    public function getAssignedAttributes()
    {
        return $this->getData('assigned_attributes');
    }

    public function getGroup()
    {
        return $this->getData('group');
    }
}
