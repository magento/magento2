<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Model\ResourceModel\Swatch as SwatchResource;
use Magento\Swatches\Model\Swatch;

/**
 * Plugin model for Catalog Resource Attribute
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class EavAttribute
{
    const DEFAULT_STORE_ID = 0;

    /**
     * @var SwatchResource
     */
    private $swatchResource;

    /**
     * Base option title used for string operations to detect is option already exists or new
     */
    const BASE_OPTION_TITLE = 'option';

    /**
     * Prefix added to option value added through API
     */
    private const API_OPTION_PREFIX = 'id_';

    /**
     * @var \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory
     */
    protected $swatchCollectionFactory;

    /**
     * @var \Magento\Swatches\Model\SwatchFactory
     */
    protected $swatchFactory;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * Array which contains links for new created attributes for swatches
     *
     * @var array
     */
    protected $dependencyArray = [];

    /**
     * Swatch existing status
     *
     * @var bool
     */
    protected $isSwatchExists;

    /**
     * Serializer from arrays to string.
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory
     * @param \Magento\Swatches\Model\SwatchFactory $swatchFactory
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param Json|null $serializer
     * @param SwatchResource|null $swatchResource
     */
    public function __construct(
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory,
        \Magento\Swatches\Model\SwatchFactory $swatchFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        ?Json $serializer = null,
        ?SwatchResource $swatchResource = null
    ) {
        $this->swatchCollectionFactory = $collectionFactory;
        $this->swatchFactory = $swatchFactory;
        $this->swatchHelper = $swatchHelper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->create(Json::class);
        $this->swatchResource = $swatchResource ?: ObjectManager::getInstance()->create(SwatchResource::class);
    }

    /**
     * Set base data to Attribute
     *
     * @param Attribute $attribute
     * @return void
     */
    public function beforeBeforeSave(Attribute $attribute)
    {
        if ($this->swatchHelper->isSwatchAttribute($attribute)) {
            $this->setProperOptionsArray($attribute);
            $this->validateOptions($attribute);
            $this->swatchHelper->assembleAdditionalDataEavAttribute($attribute);
        }
        $this->convertSwatchToDropdown($attribute);
    }

    /**
     * Swatch save operations
     *
     * @param Attribute $attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function afterAfterSave(Attribute $attribute)
    {
        if ($this->swatchHelper->isSwatchAttribute($attribute)) {
            $this->processSwatchOptions($attribute);
            $this->saveDefaultSwatchOptionValue($attribute);
            $this->saveSwatchParams($attribute);
        }
    }

    /**
     * Substitute suitable options and swatches arrays
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function setProperOptionsArray(Attribute $attribute)
    {
        $canReplace = false;
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            $canReplace = true;
            $defaultValue = $attribute->getData('defaultvisual');
            $optionsArray = $attribute->getData('optionvisual');
            $swatchesArray = $attribute->getData('swatchvisual');
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            $canReplace = true;
            $defaultValue = $attribute->getData('defaulttext');
            $optionsArray = $attribute->getData('optiontext');
            $swatchesArray = $attribute->getData('swatchtext');
        }
        if ($canReplace == true) {
            if (!empty($optionsArray)) {
                $attribute->setData('option', $optionsArray);
            }
            if (!empty($defaultValue)) {
                $attribute->setData('default', $defaultValue);
            } else {
                $attribute->setData('default', [0 => $attribute->getDefaultValue()]);
            }
            if (!empty($swatchesArray)) {
                $attribute->setData('swatch', $swatchesArray);
            }
        }
    }

    /**
     * Prepare attribute for conversion from any swatch type to dropdown
     *
     * @param Attribute $attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function convertSwatchToDropdown(Attribute $attribute)
    {
        if ($attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_DROPDOWN) {
            $additionalData = $attribute->getData('additional_data');
            if (!empty($additionalData)) {
                $additionalData = $this->serializer->unserialize($additionalData);
                if (is_array($additionalData) && isset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY])) {
                    $option = $attribute->getOption() ?: [];
                    $this->cleanEavAttributeOptionSwatchValues($option);
                    unset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY]);
                    $attribute->setData('additional_data', $this->serializer->serialize($additionalData));
                }
            }
        }
    }

    /**
     * Creates array which link new option ids
     *
     * @param Attribute $attribute
     * @return Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processSwatchOptions(Attribute $attribute)
    {
        $optionsArray = $attribute->getData('option');

        if (!empty($optionsArray) && is_array($optionsArray)) {
            $optionsArray = $this->prepareOptionIds($optionsArray);
            $adminStoreAttribute = clone $attribute;
            $adminStoreAttribute->setStoreId(self::DEFAULT_STORE_ID);
            $attributeSavedOptions = $adminStoreAttribute->getSource()->getAllOptions();
            $this->prepareOptionLinks($optionsArray, $attributeSavedOptions);
        }

        return $attribute;
    }

    /**
     * Get options array without deleted items
     *
     * @param array $optionsArray
     * @return array
     */
    protected function prepareOptionIds(array $optionsArray)
    {
        if (isset($optionsArray['value']) && is_array($optionsArray['value'])) {
            foreach (array_keys($optionsArray['value']) as $optionId) {
                if (isset($optionsArray['delete']) && isset($optionsArray['delete'][$optionId])
                    && $optionsArray['delete'][$optionId] == 1
                ) {
                    unset($optionsArray['value'][$optionId]);
                }
            }
        }
        return $optionsArray;
    }

    /**
     * Create links for non existed swatch options
     *
     * @param array $optionsArray
     * @param array $attributeSavedOptions
     * @return void
     */
    protected function prepareOptionLinks(array $optionsArray, array $attributeSavedOptions)
    {
        $dependencyArray = [];
        if (is_array($optionsArray['value'])) {
            $options = array_column($attributeSavedOptions, 'value', 'label');
            foreach ($optionsArray['value'] as $id => $labels) {
                $dependencyArray[$id] = $options[$labels[self::DEFAULT_STORE_ID]];
            }
        }

        $this->dependencyArray = $dependencyArray;
    }

    /**
     * Save all Swatches data
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function saveSwatchParams(Attribute $attribute)
    {
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            $this->processVisualSwatch($attribute);
            $attributeOptions = $attribute->getOptiontext() ?: [];
            $this->cleanTextSwatchValuesAfterSwitch($attributeOptions);
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            $this->processTextualSwatch($attribute);
        }
    }

    /**
     * Save Visual Swatch data
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function processVisualSwatch(Attribute $attribute)
    {
        $swatchArray = $attribute->getData('swatch/value');
        if (isset($swatchArray) && is_array($swatchArray)) {
            foreach ($swatchArray as $optionId => $value) {
                $optionId = $this->getAttributeOptionId($optionId);
                $isOptionForDelete = $this->isOptionForDelete($attribute, $optionId);
                if ($optionId === null || $isOptionForDelete) {
                    //option was deleted by button with basket
                    continue;
                }
                $swatch = $this->loadSwatchIfExists($optionId, self::DEFAULT_STORE_ID);

                $swatchType = $this->determineSwatchType($value);

                $this->saveSwatchData($swatch, $optionId, self::DEFAULT_STORE_ID, $swatchType, $value);
                $this->isSwatchExists = null;
            }
        }
    }

    /**
     * Clean swatch option values after switching to the dropdown type.
     *
     * @param array $attributeOptions
     * @param int|null $swatchType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function cleanEavAttributeOptionSwatchValues(array $attributeOptions, ?int $swatchType = null)
    {
        if (count($attributeOptions) && isset($attributeOptions['value'])) {
            $optionsIDs = array_keys($attributeOptions['value']);

            $this->swatchResource->clearSwatchOptionByOptionIdAndType($optionsIDs, $swatchType);
        }
    }

    /**
     * Cleaning the text type of swatch option values after switching.
     *
     * @param array $attributeOptions
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function cleanTextSwatchValuesAfterSwitch(array $attributeOptions)
    {
        $this->cleanEavAttributeOptionSwatchValues($attributeOptions, Swatch::SWATCH_TYPE_TEXTUAL);
    }

    /**
     * Get the visual swatch type based on its value
     *
     * @param string $value
     * @return int
     */
    private function determineSwatchType($value)
    {
        $swatchType = Swatch::SWATCH_TYPE_EMPTY;
        if (!empty($value) && $value[0] == '#') {
            $swatchType = Swatch::SWATCH_TYPE_VISUAL_COLOR;
        } elseif (!empty($value) && $value[0] == '/') {
            $swatchType = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
        }
        return $swatchType;
    }

    /**
     * Save Textual Swatch data
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function processTextualSwatch(Attribute $attribute)
    {
        $swatchArray = $attribute->getData('swatch/value');
        if (isset($swatchArray) && is_array($swatchArray)) {
            foreach ($swatchArray as $optionId => $storeValues) {
                $optionId = $this->getAttributeOptionId($optionId);
                $isOptionForDelete = $this->isOptionForDelete($attribute, $optionId);
                if ($optionId === null || $isOptionForDelete) {
                    //option was deleted by button with basket
                    continue;
                }
                $defaultSwatchValue = reset($storeValues);
                foreach ($storeValues as $storeId => $value) {
                    if ($value === null || $value === '') {
                        $value = $defaultSwatchValue;
                    }
                    $swatch = $this->loadSwatchIfExists($optionId, $storeId);
                    $swatch->isDeleted($isOptionForDelete);
                    $this->saveSwatchData(
                        $swatch,
                        $optionId,
                        $storeId,
                        \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL,
                        $value
                    );
                    $this->isSwatchExists = null;
                }
            }
        }
    }

    /**
     * Get option id. If it not exist get it from dependency link array
     *
     * @param integer $optionId
     * @return int
     */
    protected function getAttributeOptionId($optionId)
    {
        if (strpos((string)$optionId, self::BASE_OPTION_TITLE) === 0 ||
            strpos((string)$optionId, self::API_OPTION_PREFIX) === 0) {
            $optionId = $this->dependencyArray[$optionId] ?? null;
        }
        return $optionId;
    }

    /**
     * Check if is option for delete
     *
     * @param Attribute $attribute
     * @param integer $optionId
     * @return bool
     */
    protected function isOptionForDelete(Attribute $attribute, $optionId)
    {
        $isOptionForDelete = $attribute->getData('option/delete/' . $optionId);
        return isset($isOptionForDelete) && $isOptionForDelete;
    }

    /**
     * Load swatch if it exists in database
     *
     * @param int $optionId
     * @param int $storeId
     * @return Swatch
     */
    protected function loadSwatchIfExists($optionId, $storeId)
    {
        $collection = $this->swatchCollectionFactory->create();
        $collection->addFieldToFilter('option_id', $optionId);
        $collection->addFieldToFilter('store_id', $storeId);
        $collection->setPageSize(1);

        $loadedSwatch = $collection->getFirstItem();
        if ($loadedSwatch->getId()) {
            $this->isSwatchExists = true;
        }
        return $loadedSwatch;
    }

    /**
     * Save operation
     *
     * @param Swatch $swatch
     * @param integer $optionId
     * @param integer $storeId
     * @param integer $type
     * @param string $value
     * @return void
     */
    protected function saveSwatchData($swatch, $optionId, $storeId, $type, $value)
    {
        if ($this->isSwatchExists) {
            $swatch->setData('type', $type);
            $swatch->setData('value', $value);
        } else {
            $swatch->setData('option_id', $optionId);
            $swatch->setData('store_id', $storeId);
            $swatch->setData('type', $type);
            $swatch->setData('value', $value);
        }
        $swatch->save();
    }

    /**
     * Save default swatch value using Swatch model instead of Eav model
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function saveDefaultSwatchOptionValue(Attribute $attribute)
    {
        if (!$this->swatchHelper->isSwatchAttribute($attribute)) {
            return;
        }
        $defaultValue = $attribute->getData('default/0');
        if (!empty($defaultValue)) {
            /** @var \Magento\Swatches\Model\Swatch $swatch */
            $swatch = $this->swatchFactory->create();
            $swatch->getResource()->saveDefaultSwatchOption(
                $attribute->getId(),
                $this->getAttributeOptionId($defaultValue)
            );
        }
    }

    /**
     * Validate that attribute options exist
     *
     * @param Attribute $attribute
     * @return bool
     * @throws InputException
     */
    protected function validateOptions(Attribute $attribute)
    {
        $options = null;
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            $options = $attribute->getData('optionvisual');
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            $options = $attribute->getData('optiontext');
        }
        if ($options && !$this->isOptionsValid($options, $attribute)) {
            throw new InputException(__('Admin is a required field in each row'));
        }
        return true;
    }

    /**
     * Check if attribute options are valid
     *
     * @param array $options
     * @param Attribute $attribute
     * @return bool
     */
    protected function isOptionsValid(array $options, Attribute $attribute)
    {
        if (!isset($options['value'])) {
            return false;
        }
        foreach ($options['value'] as $optionId => $option) {
            // do not validate options marked as deleted
            if ($this->isOptionForDelete($attribute, $optionId)) {
                continue;
            }
            if (!isset($option[0]) || $option[0] === '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Modifies Attribute::usesSource() response
     *
     * Returns true if attribute type is swatch
     *
     * @param Attribute $attribute
     * @param bool $result
     * @return bool
     */
    public function afterUsesSource(Attribute $attribute, $result)
    {
        if ($this->swatchHelper->isSwatchAttribute($attribute)) {
            return true;
        }
        return $result;
    }
}
