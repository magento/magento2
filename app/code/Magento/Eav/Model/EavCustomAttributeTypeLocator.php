<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;

/**
 * Class to locate types for Eav custom attributes
 */
class EavCustomAttributeTypeLocator implements CustomAttributeTypeLocatorInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $stringUtility;

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
     * @codeCoverageIgnore
     * @param AttributeRepositoryInterface $attributeRepository Attribute repository service
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtility
     * @param array $serviceEntityTypeMap Service Entity Map
     * <pre>
     * [
     *      'ServiceInterfaceA' => 'EavEntityType1',
     *      'ServiceInterfaceB' => 'EavEntityType2'
     * ]
     * </pre>
     * @param array $serviceBackendModelDataInterfaceMap Backend Model and DataInterface map for a service
     * <pre>
     * [
     *      'ServiceInterfaceA' => ['BackendType1' => 'ServiceDataInterface1'],
     *      'ServiceInterfaceB' => [
     *                              'BackendType2' => 'ServiceDataInterface2',
     *                              'BackendType3' => 'ServiceDataInterface3'
     *                             ]
     * ]
     * </pre>
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Stdlib\StringUtils $stringUtility,
        array $serviceEntityTypeMap = [],
        array $serviceBackendModelDataInterfaceMap = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->stringUtility = $stringUtility;
        $this->serviceEntityTypeMap = $serviceEntityTypeMap;
        $this->serviceBackendModelDataInterfaceMap = $serviceBackendModelDataInterfaceMap;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getType($attributeCode, $serviceClass)
    {
        if (!$serviceClass || !$attributeCode || !isset($this->serviceEntityTypeMap[$serviceClass])
            || !isset($this->serviceBackendModelDataInterfaceMap[$serviceClass])
        ) {
            return null;
        }

        try {
            $attribute = $this->attributeRepository->get($this->serviceEntityTypeMap[$serviceClass], $attributeCode);
            $backendModel = $attribute->getBackendModel();
        } catch (NoSuchEntityException $e) {
            return null;
        }

        //If empty backend model, check if it can be derived
        if (empty($backendModel)) {
            $backendModelClass = sprintf(
                'Magento\Eav\Model\Attribute\Data\%s',
                $this->stringUtility->upperCaseWords($attribute->getFrontendInput())
            );
            $backendModel = class_exists($backendModelClass) ? $backendModelClass : null;
        }

        $dataInterface = isset($this->serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel])
            ? $this->serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel]
            : null;

        return $dataInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllServiceDataInterfaces()
    {
        $dataInterfaceArray = [];
        if (!$this->serviceBackendModelDataInterfaceMap) {
            return [];
        } else {
            foreach ($this->serviceBackendModelDataInterfaceMap as $serviceArray) {
                foreach ($serviceArray as $dataInterface) {
                    $dataInterfaceArray[] = $dataInterface;
                }
            }
        }
        return $dataInterfaceArray;
    }
}
