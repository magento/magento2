<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\Framework\ObjectManagerInterface;

class FakeObjectManager implements ObjectManagerInterface
{
    private $instances = [];

    public function create($type, array $arguments = [])
    {
        $arguments = $this->resolveConstructorArguments($type, $arguments);
        return new $type(...$arguments);
    }

    public function get($type)
    {
        if (!isset($this->instances[$type])) {
            $this->instances[$type] = $this->create($type);
        }

        return $this->instances[$type];
    }

    public function configure(array $configuration)
    {
        // Fake object manager is not configurable
    }

    private function resolveConstructorArguments($type, array $arguments)
    {
        $constructorSignature = new \ReflectionMethod($type, '__construct');
        $resolvedArguments = [];
        foreach ($constructorSignature->getParameters() as $parameter) {
            $arguments = $this->assertParameterValue($arguments, $parameter);

            $resolvedArguments[] = $arguments[$parameter->getName()] ?? $parameter->getDefaultValue();
        }

        return $resolvedArguments;
    }

    private function assertParameterValue(array $arguments, $parameter): array
    {
        if (!isset($arguments[$parameter->getName()]) && !$parameter->isDefaultValueAvailable()) {
            new \RuntimeException(
                sprintf(
                    'Cannot instantiate %s without default value for constructor argument $%s',
                    $parameter->getDeclaringClass()->getName(),
                    $parameter->getName()
                )
            );
        }
        return $arguments;
    }
}
