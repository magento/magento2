<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\AbstractModel;

/**
 * Custom layout file attribute.
 */
abstract class AbstractLayoutUpdate extends AbstractBackend
{
    public const VALUE_USE_UPDATE_XML = '__existing__';

    /**
     * Extract attribute value.
     *
     * @param AbstractModel $model
     * @throws LocalizedException
     * @return mixed
     */
    private function extractAttributeValue(AbstractModel $model)
    {
        $code = $this->getAttribute()->getAttributeCode();
        $data = $model->getData();
        //Custom attributes must not be initialized if they have not already been or it will break the saving process.
        if (array_key_exists(AbstractModel::CUSTOM_ATTRIBUTES, $data)
            && array_key_exists($code, $data[AbstractModel::CUSTOM_ATTRIBUTES])) {
            return $model->getCustomAttribute($code)->getValue();
        } elseif (array_key_exists($code, $data)) {
            return $data[$code];
        }

        return null;
    }

    /**
     * Compose list of available files (layout handles) for given entity.
     *
     * @param AbstractModel $forModel
     * @return string[]
     */
    abstract protected function listAvailableValues(AbstractModel $forModel): array;

    /**
     * Extracts prepare attribute value to be saved.
     *
     * @throws LocalizedException
     * @param AbstractModel $model
     * @return string|null
     */
    private function prepareValue(AbstractModel $model): ?string
    {
        $value = $this->extractAttributeValue($model);
        if ($value
            && $value !== self::VALUE_USE_UPDATE_XML
            && !in_array($value, $this->listAvailableValues($model), true)
        ) {
            throw new LocalizedException(__('Selected layout update is not available'));
        }
        if ($value === self::VALUE_USE_UPDATE_XML) {
            $value = null;
        }
        if (!$value) {
            $value = null;
        }

        return $value;
    }

    /**
     * Set value for the object.
     *
     * @param string|null $value
     * @param AbstractModel $forObject
     * @return void
     */
    private function setAttributeValue(?string $value, AbstractModel $forObject): void
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $data = $forObject->getData();
        if (array_key_exists(AbstractModel::CUSTOM_ATTRIBUTES, $data)
            && array_key_exists($attrCode, $data[AbstractModel::CUSTOM_ATTRIBUTES])) {
            $forObject->setCustomAttribute($attrCode, $value);
        }
        $forObject->setData($attrCode, $value);
    }

    /**
     * @inheritDoc
     *
     * @param AbstractModel $object
     */
    public function validate($object)
    {
        $valid = parent::validate($object);
        if ($valid) {
            $this->prepareValue($object);
        }

        return $valid;
    }

    /**
     * @inheritDoc
     * @param AbstractModel $object
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        $this->setAttributeValue($this->prepareValue($object), $object);

        return $this;
    }
}
