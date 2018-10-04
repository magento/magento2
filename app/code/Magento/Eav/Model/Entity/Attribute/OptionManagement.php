<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Attribute;

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
     * @inheritdoc
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
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param int $optionId
     * @throws NoSuchEntityException
     * @return void
     */
    protected function validateOption($attribute, $optionId)
    {
        if (!$attribute->getSource()->getOptionText($optionId)) {
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
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $optionLabel
     * @return void
     */
    private function setOptionValue(
        \Magento\Eav\Api\Data\AttributeOptionInterface $option,
        \Magento\Eav\Api\Data\AttributeInterface $attribute,
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
}
