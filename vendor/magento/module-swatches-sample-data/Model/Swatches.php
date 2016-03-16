<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SwatchesSampleData\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as eavAttribute;

/**
 * Class Swatches
 */
class Swatches
{
    /**
     * @var OptionCollection[]
     */
    protected $optionCollection = [];

    /**
     * @var array
     */
    protected $colorMap = [
        'Black'     => '#000000',
        'Blue'      => '#1857f7',
        'Brown'     => '#945454',
        'Gray'      => '#8f8f8f',
        'Green'     => '#53a828',
        'Lavender'  => '#ce64d4',
        'Multi'     => '#ffffff',
        'Orange'    => '#eb6703',
        'Purple'    => '#ef3dff',
        'Red'       => '#ff0000',
        'White'     => '#ffffff',
        'Yellow'    => '#ffd500',
    ];

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->convertColorToSwatches();
        $this->convertSizeToSwatches();
    }

    public function convertColorToSwatches()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'color');
        if (!$attribute) {
            return;
        }
        $attributeData['option'] = $this->addExistingOptions($attribute);
        $attributeData['frontend_input'] = 'select';
        $attributeData['swatch_input_type'] = 'visual';
        $attributeData['update_product_preview_image'] = 1;
        $attributeData['use_product_image_for_swatch'] = 0;
        $attributeData['optionvisual'] = $this->getOptionSwatch($attributeData);
        $attributeData['defaultvisual'] = $this->getOptionDefaultVisual($attributeData);
        $attributeData['swatchvisual'] = $this->getOptionSwatchVisual($attributeData);
        $attribute->addData($attributeData);
        $attribute->save();
    }

    public function convertSizeToSwatches()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'size');
        if (!$attribute) {
            return;
        }
        $attributeData['option'] = $this->addExistingOptions($attribute);
        $attributeData['frontend_input'] = 'select';
        $attributeData['swatch_input_type'] = 'text';
        $attributeData['update_product_preview_image'] = 1;
        $attributeData['use_product_image_for_swatch'] = 0;
        $attributeData['optiontext'] = $this->getOptionSwatch($attributeData);
        $attributeData['defaulttext'] = $this->getOptionDefaultText($attributeData);
        $attributeData['swatchtext'] = $this->getOptionSwatchText($attributeData);
        $attribute->addData($attributeData);
        $attribute->save();
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getOptionSwatch(array $attributeData)
    {
        $optionSwatch = ['order' => [], 'value' => [], 'delete' => []];
        $i = 0;
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['delete'][$optionKey] = '';
            $optionSwatch['order'][$optionKey] = (string)$i++;
            $optionSwatch['value'][$optionKey] = [$optionValue, ''];
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    private function getOptionSwatchVisual(array $attributeData)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            if (substr($optionValue, 0, 1) == '#' && strlen($optionValue) == 7) {
                $optionSwatch['value'][$optionKey] = $optionValue;
            } else if ($this->colorMap[$optionValue]) {
                $optionSwatch['value'][$optionKey] = $this->colorMap[$optionValue];
            } else {
                $optionSwatch['value'][$optionKey] = $this->colorMap['White'];
            }
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    private function getOptionDefaultVisual(array $attributeData)
    {
        $optionSwatch = $this->getOptionSwatchVisual($attributeData);
        return [array_keys($optionSwatch['value'])[0]];
    }

    /**
     * @param array $attributeData
     * @return array
     */
    private function getOptionSwatchText(array $attributeData)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['value'][$optionKey] = [$optionValue, ''];
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    private function getOptionDefaultText(array $attributeData)
    {
        $optionSwatch = $this->getOptionSwatchText($attributeData);
        return [array_keys($optionSwatch['value'])[0]];
    }

    /**
     * @param $attributeId
     * @return void
     */
    private function loadOptionCollection($attributeId)
    {
        if (empty($this->optionCollection[$attributeId])) {
            $this->optionCollection[$attributeId] = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($attributeId)
                ->setPositionOrder('asc', true)
                ->load();
        }
    }

    /**
     * @param eavAttribute $attribute
     * @return array
     */
    private function addExistingOptions(eavAttribute $attribute)
    {
        $options = [];
        $attributeId = $attribute->getId();
        if ($attributeId) {
            $this->loadOptionCollection($attributeId);
            /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
            foreach ($this->optionCollection[$attributeId] as $option) {
                $options[$option->getId()] = $option->getValue();
            }
        }
        return $options;
    }
}
