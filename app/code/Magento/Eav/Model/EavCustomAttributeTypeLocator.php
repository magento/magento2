<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Eav\Model\EavCustomAttributeTypeLocator\ComplexType as ComplexTypeLocator;
use Magento\Eav\Model\EavCustomAttributeTypeLocator\SimpleType as SimpleTypeLocator;
use Magento\Framework\Reflection\TypeProcessor;

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
     * @var array
     */
    private $serviceEntityTypeMap;

    /**
     * @var array
     */
    private $serviceBackendModelDataInterfaceMap;

    /**
     * @var ComplexTypeLocator
     */
    private $complexTypeLocator;

    /**
     * @var SimpleTypeLocator
     */
    private $simpleTypeLocator;

    /**
     * Initialize EavCustomAttributeTypeLocator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            return TypeProcessor::ANY_TYPE;
        }

        try {
            $attribute = $this->attributeRepository->get($this->serviceEntityTypeMap[$serviceClass], $attributeCode);
        } catch (NoSuchEntityException $e) {
            return TypeProcessor::ANY_TYPE;
        }

        $dataInterface = $this->getComplexTypeLocator()->getType(
            $attribute,
            $serviceClass,
            $this->serviceBackendModelDataInterfaceMap
        );
        return $dataInterface ?: $this->getSimpleTypeLocator()->getType($attribute);
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

    /**
     * Get complex type locator instance
     *
     * @return ComplexTypeLocator
     * @deprecated
     */
    private function getComplexTypeLocator()
    {
        if (!$this->complexTypeLocator instanceof ComplexTypeLocator) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(ComplexTypeLocator::class);
        }
        return $this->complexTypeLocator;
    }

    /**
     * Get simple type locator instance
     *
     * @return SimpleTypeLocator
     * @deprecated
     */
    private function getSimpleTypeLocator()
    {
        if (!$this->simpleTypeLocator instanceof SimpleTypeLocator) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(SimpleTypeLocator::class);
        }
        return $this->simpleTypeLocator;
    }
}
