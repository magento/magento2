<?php
/**
 *
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

namespace Magento\Eav\Api\Data;

use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\MetadataServiceInterface;

/**
 * DataBuilder class for \Magento\Eav\Api\Data\AttributeGroupInterface
 */
class AttributeGroupDataBuilder extends \Magento\Framework\Api\Builder
{
    /**
     * @param ObjectFactory $objectFactory
     * @param MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string $modelClassInterface
     */
    public function __construct(
        ObjectFactory $objectFactory,
        MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = 'Magento\Eav\Api\Data\AttributeGroupInterface'
    ) {
        parent::__construct(
            $objectFactory,
            $metadataService,
            $attributeValueBuilder,
            $objectProcessor,
            $typeProcessor,
            $dataBuilderFactory,
            $objectManagerConfig,
            $modelClassInterface
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /** TODO: temporary fix while problem with hasDataChanges flag not solved. MAGETWO-30324 */
        $object = parent::create();
        $object->setDataChanges(true);
        return $object;
    }

    /**
     * @param string|null $attributeGroupName
     * @return $this
     */
    public function setAttributeGroupName($attributeGroupName)
    {
        $this->_set('attribute_group_name', $attributeGroupName);
        return $this;
    }

    /**
     * @param string|null $attributeGroupId
     * @return $this
     */
    public function setAttributeGroupId($attributeGroupId)
    {
        $this->_set('attribute_group_id', $attributeGroupId);
        return $this;
    }

    /**
     * @param int|null $attributeSetId
     * @return $this
     */
    public function setAttributeSetId($attributeSetId)
    {
        $this->_set('attribute_set_id', $attributeSetId);
        return $this;
    }
}
