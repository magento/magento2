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

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

class OptionManagement implements \Magento\Eav\Api\AttributeOptionManagementInterface
{
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected $resourceModel;

    /**
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param \Magento\Eav\Model\Resource\Entity\Attribute $resourceModel
     */
    public function __construct(
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Model\Resource\Entity\Attribute $resourceModel
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function add($entityType, $attributeCode, $option)
    {
        if (empty($attributeCode)) {
            throw new InputException('Empty attribute code');
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(sprintf('Attribute %s doesn\'t work with options', $attributeCode));
        }
        $key = 'new_option';

        $options = [];
        $options['value'][$key][0] = $option->getLabel();
        $options['order'][$key] = $option->getSortOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$key][$label->getStoreId()] = $label->getLabel();
            }
        }

        if ($option->getIsDefault()) {
            $attribute->setDefault([$key]);
        }

        $attribute->setOption($options);
        try {
            $this->resourceModel->save($attribute);
        } catch (\Exception $e) {
            throw new StateException(sprintf('Cannot save attribute %s', $attributeCode));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entityType, $attributeCode, $optionId)
    {
        if (empty($attributeCode)) {
            throw new InputException('Empty attribute code');
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(sprintf('Attribute %s doesn\'t have any option', $attributeCode));
        }

        if (!$attribute->getSource()->getOptionText($optionId)) {
            throw new NoSuchEntityException(
                sprintf('Attribute %s does not contain option with Id %s', $attribute->getId(), $optionId)
            );
        }

        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1']
            ]
        ];
        $attribute->addData($removalMarker);
        try {
            $this->resourceModel->save($attribute);
        } catch (\Exception $e) {
            throw new StateException(sprintf('Cannot save attribute %s', $attributeCode));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($entityType, $attributeCode)
    {
        if (empty($attributeCode)) {
            throw new InputException('Empty attribute code');
        }
        $attribute = $this->attributeRepository->get($entityType, $attributeCode);

        try {
            $options = $attribute->getOptions();
        } catch (\Exception $e) {
            throw new StateException(sprintf('Cannot load options for attribute %s', $attributeCode));
        }

        return $options;
    }
}
