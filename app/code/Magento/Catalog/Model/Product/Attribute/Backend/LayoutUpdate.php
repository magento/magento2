<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Allows to select a layout file to merge when rendering the product's page.
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
     * @param Product $product
     * @return string|null
     */
    private function extractValue(Product $product): ?string
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $product->getData($attrCode);
        if ($value
            && $value !== self::VALUE_USE_UPDATE_XML
            && !in_array($value, $this->manager->fetchAvailableFiles($product), true)
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
     * @param Product $object
     */
    private function setValue(?string $value, Product $object): void
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $object->setData($attrCode, $value);
    }

    /**
     * @inheritDoc
     * @param Product $object
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
     * @param Product $object
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
