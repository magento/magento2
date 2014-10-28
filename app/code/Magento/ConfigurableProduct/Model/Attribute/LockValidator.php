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
namespace Magento\ConfigurableProduct\Model\Attribute;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;

class LockValidator implements LockValidatorInterface
{
    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Check attribute lock state
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param null $attributeSet
     * @throws \Magento\Framework\Model\Exception
     * @return void
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object, $attributeSet = null)
    {
        $adapter = $this->resource->getConnection('read');
        $attrTable = $this->resource->getTableName('catalog_product_super_attribute');
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $bind = array('attribute_id' => $object->getAttributeId());
        $select = clone $adapter->select();
        $select->reset()->from(
            array('main_table' => $attrTable),
            array('psa_count' => 'COUNT(product_super_attribute_id)')
        )->join(
            array('entity' => $productTable),
            'main_table.product_id = entity.entity_id'
        )->where(
            'main_table.attribute_id = :attribute_id'
        )->group(
            'main_table.attribute_id'
        )->limit(
            1
        );

        if ($attributeSet !== null) {
            $bind['attribute_set_id'] = $attributeSet;
            $select->where('entity.attribute_set_id = :attribute_set_id');
        }

        if ($adapter->fetchOne($select, $bind)) {
            throw new \Magento\Framework\Model\Exception(__('This attribute is used in configurable products.'));
        }
    }
}
