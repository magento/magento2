<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

/**
 * Interface to validate attribute removal from an attribute set
 */
interface AttributeSetUnassignValidatorInterface
{
    /**
     * Validate attribute
     *
     * @param AbstractModel $attribute
     * @param int $attributeSetId
     * @return void
     * @throws LocalizedException
     */
    public function validate(AbstractAttribute $attribute, int $attributeSetId): void;
}
