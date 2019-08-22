<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeInterface as EavAttributeInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Eav Option Management
 */
class OptionManagement implements \Magento\Eav\Api\AttributeOptionManagementInterface
{
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $resourceModel;

    /**
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Add option to attribute.
     *
     * @param int $entityType
     * @param string $attributeCode
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function add($entityType, $attributeCode, $option)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('The attribute code is empty. Enter the code and try again.'));
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('The "%1" attribute doesn\'t work with options.', $attributeCode));
        }

        $optionLabel = $option->getLabel();
        $optionId = $this->getOptionId($option);
        $options = [];
        $options['value'][$optionId][0] = $optionLabel;
        $options['order'][$optionId] = $option->getSortOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
        }

        if (!$this->isAttributeOptionLabelExists($attribute, (string) $options['value'][$optionId][0])) {
            throw new InputException(
                __(
                    'Admin store attribute option label "%1" is already exists.',
                    $options['value'][$optionId][0]
                )
            );
        }

        if ($option->getIsDefault()) {
            $attribute->setDefault([$optionId]);
        }

        $attribute->setOption($options);
        try {
            $this->resourceModel->save($attribute);
            if ($optionLabel && $attribute->getAttributeCode()) {
                $this->setOptionValue($option, $attribute, $optionLabel);
            }
        } catch (\Exception $e) {
            throw new StateException(__('The "%1" attribute can\'t be saved.', $attributeCode));
        }

        return $this->getOptionId($option);
    }

    /**
     * @inheritdoc
     */
    public function delete($entityType, $attributeCode, $optionId)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('The attribute code is empty. Enter the code and try again.'));
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('The "%1" attribute has no option.', $attributeCode));
        }
        $this->validateOption($attribute, $optionId);

        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1'],
            ],
        ];
        $attribute->addData($removalMarker);
        try {
            $this->resourceModel->save($attribute);
        } catch (\Exception $e) {
            throw new StateException(__('The "%1" attribute can\'t be saved.', $attributeCode));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getItems($entityType, $attributeCode)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('The attribute code is empty. Enter the code and try again.'));
        }
        $attribute = $this->attributeRepository->get($entityType, $attributeCode);

        try {
            $options = $attribute->getOptions();
        } catch (\Exception $e) {
            throw new StateException(__('The options for "%1" attribute can\'t be loaded.', $attributeCode));
        }

        return $options;
    }

    /**
     * Validate option
     *
     * @param EavAttributeInterface $attribute
     * @param int $optionId
     * @return void
     * @throws NoSuchEntityException
     */
    protected function validateOption($attribute, $optionId)
    {
        if ($attribute->getSource()->getOptionText($optionId) === false) {
            throw new NoSuchEntityException(
                __(
                    'The "%1" attribute doesn\'t include an option with "%2" ID.',
                    $attribute->getAttributeCode(),
                    $optionId
                )
            );
        }
    }

    /**
     * Returns option id
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return string
     */
    private function getOptionId(\Magento\Eav\Api\Data\AttributeOptionInterface $option) : string
    {
        return 'id_' . ($option->getValue() ?: 'new_option');
    }

    /**
     * Set option value
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @param EavAttributeInterface $attribute
     * @param string $optionLabel
     * @return void
     */
    private function setOptionValue(
        \Magento\Eav\Api\Data\AttributeOptionInterface $option,
        EavAttributeInterface $attribute,
        string $optionLabel
    ) {
        $optionId = $attribute->getSource()->getOptionId($optionLabel);
        if ($optionId) {
            $option->setValue($attribute->getSource()->getOptionId($optionId));
        } elseif (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                if ($optionId = $attribute->getSource()->getOptionId($label->getLabel())) {
                    $option->setValue($attribute->getSource()->getOptionId($optionId));
                    break;
                }
            }
        }
    }

    /**
     * Checks if the incoming attribute option label for admin store is already exists.
     *
     * @param EavAttributeInterface $attribute
     * @param string $adminStoreLabel
     * @param int $storeId
     * @return bool
     */
    private function isAttributeOptionLabelExists(
        EavAttributeInterface $attribute,
        string $adminStoreLabel,
        int $storeId = 0
    ) :bool {
        $attribute->setStoreId($storeId);

        foreach ($attribute->getSource()->toOptionArray() as $existingAttributeOption) {
            if ($existingAttributeOption['label'] === $adminStoreLabel) {
                return false;
            }
        }

        return true;
    }
}
