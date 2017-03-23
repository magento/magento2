<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class TypeResolver
 */
class TypeResolver
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var array
     */
    private $typeMapping = [
        \Magento\SalesRule\Model\Rule::class => \Magento\SalesRule\Api\Data\RuleInterface::class,
        \Magento\SalesRule\Model\Rule\Interceptor::class => \Magento\SalesRule\Api\Data\RuleInterface::class,
        \Magento\SalesRule\Model\Rule\Proxy::class => \Magento\SalesRule\Api\Data\RuleInterface::class
    ];

    /**
     * TypeResolver constructor.
     * @param MetadataPool $metadataPool
     */
    public function __construct(MetadataPool $metadataPool)
    {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param object $type
     * @return string
     * @throws \Exception
     */
    public function resolve($type)
    {
        // @todo remove after MAGETWO-52608 resolved
        $className = get_class($type);
        if (isset($this->typeMapping[$className])) {
            return $this->typeMapping[$className];
        }

        $reflectionClass = new \ReflectionClass($type);
        $interfaceNames = $reflectionClass->getInterfaceNames();
        $dataInterfaces = [];
        foreach ($interfaceNames as $interfaceName) {
            if (strpos($interfaceName, '\Api\Data\\')) {
                $dataInterfaces[] = $interfaceName;
            }
        }

        if (count($dataInterfaces) == 0) {
            throw new \Exception('Unable to determine data interface for ' . $className);
        }

        foreach ($dataInterfaces as $dataInterface) {
            if ($this->metadataPool->hasConfiguration($dataInterface)) {
                $this->typeMapping[$className] = $dataInterface;
            }
        }
        if (empty($this->typeMapping[$className])) {
            $this->typeMapping[$className] = reset($dataInterfaces);
        }
        return $this->typeMapping[$className];
    }
}
