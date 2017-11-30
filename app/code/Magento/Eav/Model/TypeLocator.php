<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttribute\TypeLocatorInterface;

/**
 * Class to locate types for Eav custom attributes
 */
class TypeLocator implements TypeLocatorInterface
{
    /**
     * @var \Magento\Framework\Webapi\CustomAttribute\TypeLocatorInterface[]
     */
    private $typeLocators;

    /**
     * Initialize CustomAttributeTypeLocator
     *
     * @param \Magento\Framework\Webapi\CustomAttribute\TypeLocatorInterface[] $typeLocators
     */
    public function __construct(
        array $typeLocators = []
    ) {
        $this->typeLocators = $typeLocators;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($attributeCode, $entityType)
    {
        foreach ($this->typeLocators as $typeLocator) {
            $type = $typeLocator->getType($attributeCode, $entityType);
            if ($type !== TypeProcessor::NORMALIZED_ANY_TYPE) {
                return $type;
            }
        }

        return TypeProcessor::NORMALIZED_ANY_TYPE;
    }
}
