<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Attribute\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;

class AttributeSetUnassignValidator implements AttributeSetUnassignValidatorInterface
{
    /**
     * @var array
     */
    private array $unassignable;

    /**
     * @param Config $attributeConfig
     */
    public function __construct(
        private readonly Config $attributeConfig
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractAttribute $attribute, int $attributeSetId): void
    {
        if (!isset($this->unassignable)) {
            $this->unassignable = $this->attributeConfig->getAttributeNames('unassignable');
        }
        if (in_array($attribute->getAttributeCode(), $this->unassignable)) {
            throw new LocalizedException(
                __("The system attribute can't be deleted.")
            );
        }
    }
}
