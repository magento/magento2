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

namespace Mtf\Util\Generate\Fixture;

use Magento\Framework\App\Resource;
use Magento\Framework\ObjectManager;

/**
 * Class FieldsProvider
 *
 */
class FieldsProvider implements FieldsProviderInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @constructor
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->eavConfig = $objectManager->create('Magento\Eav\Model\Config');
        $this->resource = $objectManager->create('Magento\Framework\App\Resource');
    }

    /**
     * Collect fields for the entity based on its type
     *
     * @param array $fixture
     * @return array
     */
    public function getFields(array $fixture)
    {
        $method = $fixture['type'] . 'CollectFields';
        if (!method_exists($this, $method)) {
            return [];
        }

        return $this->$method($fixture);
    }

    /**
     * Collect fields for the entity with eav type
     *
     * @param array $fixture
     * @return array
     */
    protected function eavCollectFields(array $fixture)
    {
        $entityType = $fixture['entity_type'];
        $collection = $this->eavConfig->getEntityType($entityType)->getAttributeCollection();
        $attributes = [];
        foreach ($collection as $attribute) {
            if (isset($fixture['product_type'])) {
                $applyTo = $attribute->getApplyTo();
                if (!empty($applyTo) && !in_array($fixture['product_type'], $applyTo)) {
                    continue;
                }
            }
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $code = $attribute->getAttributeCode();
            $attributes[$code] = array(
                'attribute_code' => $code,
                'backend_type' => $attribute->getBackendType(),
                'is_required' => $attribute->getIsRequired(),
                'default_value' => $attribute->getDefaultValue(),
                'input' => $attribute->getFrontendInput()
            );
        }

        return $attributes;
    }

    /**
     * Collect fields for the entity with table type
     *
     * @param array $fixture
     * @return array
     */
    protected function tableCollectFields(array $fixture)
    {
        return $this->flatCollectFields($fixture);
    }

    /**
     * Collect fields for the entity with flat type
     *
     * @param array $fixture
     * @return array
     */
    protected function flatCollectFields(array $fixture)
    {
        $entityType = $fixture['entity_type'];

        /** @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
        $connection = $this->resource->getConnection('core_write');
        $fields = $connection->describeTable($entityType);

        $attributes = [];
        foreach ($fields as $code => $field) {
            $attributes[$code] = array(
                'attribute_code' => $code,
                'backend_type' => $field['DATA_TYPE'],
                'is_required' => ($field['PRIMARY'] || $field['IDENTITY']),
                'default_value' => $field['DEFAULT'],
                'input' => ''
            );
        }

        return $attributes;
    }

    /**
     * Collect fields for the entity with composite type
     *
     * @param array $fixture
     * @return array
     */
    protected function compositeCollectFields(array $fixture)
    {
        $entityTypes = $fixture['entities'];

        /** @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
        $connection = $this->resource->getConnection('core_write');
        $fields = [];
        foreach ($entityTypes as $entityType) {
            $fields = array_merge($fields, $connection->describeTable($entityType));
        }

        $attributes = [];
        foreach ($fields as $code => $field) {
            $attributes[$code] = [
                'attribute_code' => $code,
                'backend_type' => $field['DATA_TYPE'],
                'is_required' => ($field['PRIMARY'] || $field['IDENTITY']),
                'default_value' => $field['DEFAULT'],
                'input' => ''
            ];
        }

        return $attributes;
    }
}
