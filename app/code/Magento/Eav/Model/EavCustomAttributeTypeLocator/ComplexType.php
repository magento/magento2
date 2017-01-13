<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\EavCustomAttributeTypeLocator;

/**
 * Class to locate complex types for EAV custom attributes
 */
class ComplexType
{
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $stringUtility;

    /**
     * Initialize dependencies
     *
     * @codeCoverageIgnore
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtility
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $stringUtility
    ) {
        $this->stringUtility = $stringUtility;
    }

    /**
     * Get attribute type based on its backend model.
     * 
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $serviceClass
     * @param $serviceBackendModelDataInterfaceMap array
     * @return string|null
     */
    public function getType($attribute, $serviceClass, $serviceBackendModelDataInterfaceMap)
    {
        $backendModel = $attribute->getBackendModel();
        //If empty backend model, check if it can be derived
        if (empty($backendModel)) {
            $backendModelClass = sprintf(
                'Magento\Eav\Model\Attribute\Data\%s',
                $this->stringUtility->upperCaseWords($attribute->getFrontendInput())
            );
            $backendModel = class_exists($backendModelClass) ? $backendModelClass : null;
        }

        $dataInterface = isset($serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel])
            ? $serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel]
            : null;

        return $dataInterface;
    }
}
