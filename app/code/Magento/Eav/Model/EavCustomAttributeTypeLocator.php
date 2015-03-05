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
     * @var array
     */
    private $serviceEntityTypeMap;

    /**
     * @var array
     */
    private $serviceBackendModelDataInterfaceMap;

    /**
     * Initialize EavCustomAttributeTypeLocator
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $serviceEntityTypeMap
     * @param array $serviceBackendModelDataInterfaceMap
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        array $serviceEntityTypeMap = [],
        array $serviceBackendModelDataInterfaceMap = []
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
        $entityType = $this->serviceEntityTypeMap[$serviceClass];
        if (!$entityType) {
            return null;
        }

        try {
            $backendModel = $this->attributeRepository->get($entityType, $attributeCode)->getBackendModel();
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $dataInterfaceBackendTypeMap = $this->serviceBackendModelDataInterfaceMap[$serviceClass];
        $dataInterface = isset($dataInterfaceBackendTypeMap[$backendModel])
            ? $dataInterfaceBackendTypeMap[$backendModel]
            : null;

        return $dataInterface;
    }
}
