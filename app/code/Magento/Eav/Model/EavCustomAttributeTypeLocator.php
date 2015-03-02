<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;

class EavCustomAttributeTypeLocator implements CustomAttributeTypeLocatorInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\Object
     */
    private $serviceEntityTypeMap;

    /**
     * @var \Magento\Framework\Object
     */
    private $serviceBackendModelDataInterfaceMap;

    /**
     * Initialize EavCustomAttributeTypeLocator
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Object $serviceEntityTypeMap
     * @param \Magento\Framework\Object $serviceBackendModelDataInterfaceMap
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Object $serviceEntityTypeMap,
        \Magento\Framework\Object $serviceBackendModelDataInterfaceMap
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->serviceEntityTypeMap = $serviceEntityTypeMap;
        $this->serviceBackendModelDataInterfaceMap = $serviceBackendModelDataInterfaceMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($attributeCode, $serviceClass)
    {
        $entityType = $this->serviceEntityTypeMap->getData($serviceClass);
        if (!$entityType) {
            return null;
        }

        try {
            $backendModel = $this->attributeRepository->get($entityType, $attributeCode)->getBackendModel();
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $dataInterfaceBackendTypeMap = $this->serviceBackendModelDataInterfaceMap->getData($serviceClass);
        $dataInterface = isset($dataInterfaceBackendTypeMap[$backendModel])
            ? $dataInterfaceBackendTypeMap[$backendModel]
            : null;

        return $dataInterface;
    }
}
