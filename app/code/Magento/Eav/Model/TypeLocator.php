<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;

/**
 * Class to locate types for Eav custom attributes
 */
class TypeLocator implements CustomAttributeTypeLocatorInterface
{
    /**
     * @var \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface[]
     */
    private $typeLocators;

    /**
     * @var ServiceTypeListInterface
     */
    private $serviceTypeList;

    /**
     * Initialize TypeLocator
     *
     * @param ServiceTypeListInterface $serviceTypeList
     * @param \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface[] $typeLocators
     */
    public function __construct(
        ServiceTypeListInterface $serviceTypeList,
        array $typeLocators = []
    ) {
        $this->typeLocators = $typeLocators;
        $this->serviceTypeList = $serviceTypeList;
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

    /**
     * {@inheritDoc}
     */
    public function getAllServiceDataInterfaces()
    {
        return $this->serviceTypeList->getDataTypes();
    }
}
