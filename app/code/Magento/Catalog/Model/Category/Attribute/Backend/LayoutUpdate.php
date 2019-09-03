<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Category\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Allows to select a layout file to merge when rendering the category's page.
 */
class LayoutUpdate extends AbstractBackend
{
    public const VALUE_USE_UPDATE_XML = '__existing__';

    /**
     * @var LayoutUpdateManager
     */
    private $manager;

    /**
     * @param LayoutUpdateManager $manager
     */
    public function __construct(LayoutUpdateManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Extracts the attributes value from given entity.
     *
     * @throws LocalizedException
     * @param Category $category
     * @return string|null
     */
    private function extractValue(Category $category): ?string
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $attrValue = $category->getCustomAttribute($attrCode);
        $value = $category->getData($attrCode) ?? ($attrValue ? $attrValue->getValue() : null);
        if ($value
            && $value !== self::VALUE_USE_UPDATE_XML
            && !in_array($value, $this->manager->fetchAvailableFiles($category), true)
        ) {
            throw new LocalizedException(__('Selected layout update is not available'));
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
     * @param Category $object
     */
    private function setValue(?string $value, Category $object): void
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $object->setCustomAttribute($attrCode, $value);
        $object->setData($attrCode, $value);
    }

    /**
     * @inheritDoc
     * @param Category $object
     */
    public function validate($object)
    {
        $valid = parent::validate($object);
        if ($valid) {
            $this->extractValue($object);
        }

        return $valid;
    }

    /**
     * @inheritDoc
     * @param Category $object
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        $value = $this->extractValue($object);
        if ($value === self::VALUE_USE_UPDATE_XML) {
            $value = null;
        }
        $this->setValue($value, $object);

        return $this;
    }
}
