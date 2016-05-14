<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class TypeResolver
 */
class TypeResolver
{
    /**
     * @var array
     */
    private $typeMapping = [
        \Magento\SalesRule\Model\Rule::class => \Magento\SalesRule\Api\Data\RuleInterface::class,
        \Magento\SalesRule\Model\Rule\Interceptor::class => \Magento\SalesRule\Api\Data\RuleInterface::class
    ];

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
                $dataInterfaces[] = isset($this->config[$interfaceName])
                    ? $this->config[$interfaceName] : $interfaceName;
            }
        }

        if (count($dataInterfaces) == 0) {
            throw new \Exception('Unable to determine data interface for ' . $className);
        }

        return reset($dataInterfaces);
    }
}
