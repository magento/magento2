<?php
/**
 * @author      Magento Core Team <core@magentocommerce.com>
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Api\ProductAttributeOptionUpdateInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeOptionUpdateInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Exception\InputException;

/**
 * Option management model for product attribute.
 */
class OptionManagement implements ProductAttributeOptionManagementInterface, ProductAttributeOptionUpdateInterface
{
    /**
     * @var AttributeOptionManagementInterface
     */
    protected $eavOptionManagement;

    /**
     * @var AttributeOptionUpdateInterface
     */
    private $eavOptionUpdate;

    /**
     * @param AttributeOptionManagementInterface $eavOptionManagement
     * @param AttributeOptionUpdateInterface $eavOptionUpdate
     */
    public function __construct(
        AttributeOptionManagementInterface $eavOptionManagement,
        AttributeOptionUpdateInterface $eavOptionUpdate
    ) {
        $this->eavOptionManagement = $eavOptionManagement;
        $this->eavOptionUpdate = $eavOptionUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getItems($attributeCode)
    {
        return $this->eavOptionManagement->getItems(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function add($attributeCode, $option)
    {
        return $this->eavOptionManagement->add(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $option
        );
    }

    /**
     * @inheritdoc
     */
    public function update(string $attributeCode, int $optionId, AttributeOptionInterface $option): bool
    {
        return $this->eavOptionUpdate->update(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionId,
            $option
        );
    }

    /**
     * @inheritdoc
     */
    public function delete($attributeCode, $optionId)
    {
        if (empty($optionId)) {
            throw new InputException(__('Invalid option id %1', $optionId));
        }

        return $this->eavOptionManagement->delete(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionId
        );
    }
}
