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
        if (!$serviceClass || !$attributeCode || !isset($this->serviceEntityTypeMap[$serviceClass])
            || !isset($this->serviceBackendModelDataInterfaceMap[$serviceClass])
        ) {
            return null;
        }

        try {
            $backendModel = $this->attributeRepository
                ->get($this->serviceEntityTypeMap[$serviceClass], $attributeCode)
                ->getBackendModel();
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $dataInterface = isset($this->serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel])
            ? $this->serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel]
            : null;

        return $dataInterface;
    }
}
