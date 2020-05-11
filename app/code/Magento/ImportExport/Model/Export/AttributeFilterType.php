<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;

/**
 * The class serves as a wrapper for the export model's static methods,
 * to make them testable by unit tests.
 */
class AttributeFilterType
{
    /**
     * Determine filter type for specified attribute.
     *
     * @param Attribute $attribute
     * @return string
     * @throws LocalizedException
     */
    public function getAttributeFilterType(Attribute $attribute)
    {
        return Export::getAttributeFilterType($attribute);
    }

    /**
     * Determine filter type for static attribute.
     *
     * @param Attribute $attribute
     * @return string
     */
    public function getStaticAttributeFilterType(Attribute $attribute)
    {
        return Export::getStaticAttributeFilterType($attribute);
    }
}
