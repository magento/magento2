<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Catalog\Model\Attribute\Backend\AbstractLayoutUpdate as Backend;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * List of layout updates available for a catalog entity.
 */
abstract class AbstractLayoutUpdate extends AbstractSource implements SpecificSourceInterface
{
    /**
     * @var string[]
     */
    private $optionsText;

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        $default = Backend::VALUE_NO_UPDATE;
        $defaultText = 'No update';
        $this->optionsText[$default] = $defaultText;

        return [['label' => $defaultText, 'value' => $default]];
    }

    /**
     * @inheritDoc
     */
    public function getOptionText($value)
    {
        if (is_scalar($value) && array_key_exists($value, $this->optionsText)) {
            return $this->optionsText[$value];
        }

        return false;
    }

    /**
     * Extract attribute value.
     *
     * @param CustomAttributesDataInterface|AbstractExtensibleModel $entity
     * @return mixed
     */
    private function extractAttributeValue(CustomAttributesDataInterface $entity)
    {
        $attrCode = 'custom_layout_update';
        if ($entity instanceof AbstractExtensibleModel
            && !$entity->hasData(CustomAttributesDataInterface::CUSTOM_ATTRIBUTES)
        ) {
            //Custom attributes were not loaded yet, using data array
            return $entity->getData($attrCode);
        }
        //Fallback to customAttribute method
        $attr = $entity->getCustomAttribute($attrCode);

        return $attr ? $attr->getValue() : null;
    }

    /**
     * List available layout update options for the entity.
     *
     * @param CustomAttributesDataInterface $entity
     * @return string[]
     */
    abstract protected function listAvailableOptions(CustomAttributesDataInterface $entity): array;

    /**
     * @inheritDoc
     *
     * @param CustomAttributesDataInterface|AbstractExtensibleModel $entity
     */
    public function getOptionsFor(CustomAttributesDataInterface $entity): array
    {
        $options = $this->getAllOptions();
        if ($this->extractAttributeValue($entity)) {
            $existingValue = Backend::VALUE_USE_UPDATE_XML;
            $existingLabel = 'Use existing';
            $options[] = ['label' => $existingLabel, 'value' => $existingValue];
            $this->optionsText[$existingValue] = $existingLabel;
        }
        foreach ($this->listAvailableOptions($entity) as $handle) {
            $options[] = ['label' => $handle, 'value' => $handle];
            $this->optionsText[$handle] = $handle;
        }

        return $options;
    }
}
