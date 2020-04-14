<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Update;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Update\AbstractUpdateAttributeTest;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributeType;

/**
 * Base update and assert swatch attribute data.
 */
abstract class AbstractUpdateSwatchAttributeTest extends AbstractUpdateAttributeTest
{
    /** @var SwatchAttributeType */
    private $swatchAttributeType;

    /** @var SwatchCollectionFactory */
    private $swatchCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->swatchAttributeType = $this->_objectManager->get(SwatchAttributeType::class);
        $this->swatchCollectionFactory = $this->_objectManager->get(SwatchCollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function replaceStoreCodeWithId(array $optionsArray): array
    {
        $optionsArray = parent::replaceStoreCodeWithId($optionsArray);
        foreach ($optionsArray as $key => $option) {
            if (isset($option['swatch'])) {
                $optionsArray[$key]['swatch'] = $this->prepareStoresData($option['swatch']);
            }
        }

        return $optionsArray;
    }

    /**
     * @inheritdoc
     */
    protected function getActualOptionsData(string $attributeId): array
    {
        $actualOptionsData = parent::getActualOptionsData($attributeId);
        foreach (array_keys($actualOptionsData) as $optionId) {
            $actualOptionsData[$optionId]['swatch'] = $this->getAttributeOptionSwatchValues($optionId);
        }

        return $actualOptionsData;
    }

    /**
     * @inheritdoc
     */
    protected function prepareStoreOptionsPostData(array $optionsData): array
    {
        $optionsPostData = parent::prepareStoreOptionsPostData($optionsData);
        $swatchType = $this->getSwatchType();
        $swatchOptionsPostData = [];

        foreach ($optionsData as $optionId => $option) {
            $data = [];
            $data['option' . $swatchType] = $optionsPostData[$optionId]['option'];
            $optionSwatch = $swatchType == Swatch::SWATCH_INPUT_TYPE_VISUAL ? $option['swatch'][0] : $option['swatch'];

            $data['swatch' . $swatchType] = [
                'value' => [
                    $optionId => $optionSwatch,
                ],
            ];
            if (isset($optionsPostData[$optionId]['default'])) {
                $data['default' . $swatchType] = $optionsPostData[$optionId]['default'];
            }
            $swatchOptionsPostData[] = $data;
        }

        return $swatchOptionsPostData;
    }

    /**
     * @inheritdoc
     */
    protected function prepareStoreOptionsExpectedData(array $optionsData): array
    {
        $optionsExpectedData = parent::prepareStoreOptionsExpectedData($optionsData);
        $optionsArray = $optionsExpectedData['options_array'];
        foreach (array_keys($optionsArray) as $optionId) {
            $optionsArray[$optionId]['swatch'] = $optionsData[$optionId]['swatch'];
        }

        return [
            'options_array' => $optionsArray,
            'default_value' => $optionsExpectedData['default_value'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function assertUpdateAttributeData(
        ProductAttributeInterface $attribute,
        array $expectedData
    ): void {
        $this->swatchAttributeType->isSwatchAttribute($attribute);
        parent::assertUpdateAttributeData($attribute, $expectedData);
    }

    /**
     * Get attribute option swatch values by option id.
     *
     * @param int $optionId
     * @return array
     */
    private function getAttributeOptionSwatchValues(int $optionId): array
    {
        $swatchValues = [];
        $collection = $this->swatchCollectionFactory->create();
        $collection->addFieldToFilter('option_id', $optionId);

        foreach ($collection as $item) {
            $swatchValues[$item->getData('store_id')] = $item->getData('value');
        }

        return $swatchValues;
    }

    /**
     * Get swatch type.
     *
     * @return string
     */
    abstract protected function getSwatchType(): string;
}
