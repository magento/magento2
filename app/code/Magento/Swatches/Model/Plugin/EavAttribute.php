<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Swatches\Model\Swatch;
use Magento\Framework\Unserialize\SecureUnserializer;
use Magento\Framework\App\ObjectManager;

/**
 * Plugin model for Catalog Resource Attribute
 */
class EavAttribute
{
    const DEFAULT_STORE_ID = 0;

    /**
     * Base option title used for string operations to detect is option already exists or new
     */
    const BASE_OPTION_TITLE = 'option';

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
     * @var SecureUnserializer
     */
    private $secureUnserializer;

    /**
     * @param \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory
     * @param \Magento\Swatches\Model\SwatchFactory $swatchFactory
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param SecureUnserializer|null $secureUnserializer
     */
    public function __construct(
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory,
        \Magento\Swatches\Model\SwatchFactory $swatchFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        SecureUnserializer $secureUnserializer = null
    ) {
        $this->swatchCollectionFactory = $collectionFactory;
        $this->swatchFactory = $swatchFactory;
        $this->swatchHelper = $swatchHelper;
        $this->secureUnserializer = $secureUnserializer
            ?: ObjectManager::getInstance()->get(SecureUnserializer::class);
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
     * @return void
     */
    protected function convertSwatchToDropdown(Attribute $attribute)
    {
        if ($attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_DROPDOWN) {
            $additionalData = $attribute->getData('additional_data');
            if (!empty($additionalData)) {
                $additionalData = $this->secureUnserializer->unserialize($additionalData);
                if (is_array($additionalData) && isset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY])) {
                    unset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY]);
                    $attribute->setData('additional_data', serialize($additionalData));
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
            $attributeSavedOptions = $attribute->getSource()->getAllOptions();
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
                if (isset($optionsArray['delete'][$optionId]) && $optionsArray['delete'][$optionId] == 1) {
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
            $optionCounter = 1;
            foreach (array_keys($optionsArray['value']) as $baseOptionId) {
                $dependencyArray[$baseOptionId] = $attributeSavedOptions[$optionCounter]['value'];
                $optionCounter++;
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
                foreach ($storeValues as $storeId => $value) {
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
        if (substr($optionId, 0, 6) == self::BASE_OPTION_TITLE) {
            $optionId = isset($this->dependencyArray[$optionId]) ? $this->dependencyArray[$optionId] : null;
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
            // created and removed on frontend option not exists in dependency array
            if (
                substr($defaultValue, 0, 6) == self::BASE_OPTION_TITLE &&
                isset($this->dependencyArray[$defaultValue])
            ) {
                $defaultValue = $this->dependencyArray[$defaultValue];
            }
            $swatch->getResource()->saveDefaultSwatchOption($attribute->getId(), $defaultValue);
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
            throw new InputException(__('Admin is a required field in the each row'));
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
}
