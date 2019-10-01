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

    public const VALUE_NO_UPDATE = '__no_update__';

    /**
     * Extract attribute value.
     *
     * @param AbstractModel $model
     * @return mixed
     */
    private function extractAttributeValue(AbstractModel $model)
    {
        $code = $this->getAttribute()->getAttributeCode();

        return $model->getData($code);
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
        if (!is_string($value)) {
            $value = null;
        }
        if ($value
            && $value !== self::VALUE_USE_UPDATE_XML
            && $value !== self::VALUE_NO_UPDATE
            && !in_array($value, $this->listAvailableValues($model), true)
        ) {
            throw new LocalizedException(__('Selected layout update is not available'));
        }

        return $value;
    }

    /**
     * Set value for the object.
     *
     * @param string|null $value
     * @param AbstractModel $forObject
     * @param string|null $attrCode
     * @return void
     */
    private function setAttributeValue(?string $value, AbstractModel $forObject, ?string $attrCode = null): void
    {
        $attrCode = $attrCode ?? $this->getAttribute()->getAttributeCode();
        if ($forObject->hasData(AbstractModel::CUSTOM_ATTRIBUTES)) {
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
        $value = $this->prepareValue($object);
        if ($value && ($value === self::VALUE_NO_UPDATE || $value !== self::VALUE_USE_UPDATE_XML)) {
            $this->setAttributeValue(null, $object, 'custom_layout_update');
        }
        if (!$value || $value === self::VALUE_USE_UPDATE_XML || $value === self::VALUE_NO_UPDATE) {
            $value = null;
        }
        $this->setAttributeValue($value, $object);

        return $this;
    }
}
