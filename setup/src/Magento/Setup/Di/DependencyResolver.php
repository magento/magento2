<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Di;

use Laminas\Di\Definition\ClassDefinitionInterface;
use Laminas\Di\Resolver\DependencyResolver as LaminasDependencyResolver;

/**
 * Dependency resolver fulfilling required constructor parameters only
 */
class DependencyResolver extends LaminasDependencyResolver
{
    /**
     * @inheritDoc
     */
    private function getClassDefinition(string $type): ClassDefinitionInterface
    {
        if ($this->config->isAlias($type)) {
            $type = $this->config->getClassForAlias($type) ?? $type;
        }

        return $this->definition->getClassDefinition($type);
    }

    /**
     * @inheritDoc
     */
    public function resolveParameters(string $requestedType, array $callTimeParameters = []): array
    {
        $result = parent::resolveParameters($requestedType, $callTimeParameters);
        if (empty($result)) {
            return [];
        }

        $parameters = $this->getClassDefinition($requestedType)->getParameters();
        $requiredOnlyResult = [];

        foreach ($parameters as $paramInfo) {
            if (!$paramInfo->isRequired()) {
                continue;
            }
            $name = $paramInfo->getName();
            $requiredOnlyResult[$name] = $result[$name];
        }

        return $requiredOnlyResult;
    }
}
