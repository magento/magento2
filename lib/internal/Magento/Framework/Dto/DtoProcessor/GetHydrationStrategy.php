<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\DtoProcessor;

use LogicException;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Reflection\NameFinder;
use ReflectionException;
use Zend\Code\Reflection\ClassReflection;

class GetHydrationStrategy
{
    /**
     * Strategy for setter hydration
     */
    public const HYDRATOR_STRATEGY_SETTER = 'setter';

    /**
     * Strategy for constructor parameters injection
     */
    public const HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM = 'constructor';

    /**
     * Strategy for constructor data parameter injection
     */
    public const HYDRATOR_STRATEGY_CONSTRUCTOR_DATA = 'data';

    /**
     * List of orphan parameters
     */
    public const HYDRATOR_STRATEGY_ORPHAN = 'orphan';

    /**
     * @var NameFinder
     */
    private $nameFinder;

    /**
     * @var DtoReflection
     */
    private $dtoReflection;

    /**
     * @param NameFinder $nameFinder
     * @param DtoReflection $dtoReflection
     */
    public function __construct(
        NameFinder $nameFinder,
        DtoReflection $dtoReflection
    ) {
        $this->nameFinder = $nameFinder;
        $this->dtoReflection = $dtoReflection;
    }

    /**
     * Return the strategy for values injection.
     *
     *
     * @param string $className
     * @param array $data
     * @return array
     * @throws ReflectionException
     */
    public function execute(string $className, array $data): array
    {
        // TODO:: Add a cache layer here
        $strategy = [
            self::HYDRATOR_STRATEGY_SETTER => [],
            self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [],
            self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
            self::HYDRATOR_STRATEGY_ORPHAN => [],
        ];

        $class = new ClassReflection($className);
        $realClassName = $this->dtoReflection->getRealClassName($className);
        $realClass = new ClassReflection($realClassName);

        // Enumerate parameters and types
        $paramTypes = [];
        foreach ($data as $propertyName => $propertyValue) {
            $type = $this->dtoReflection->getPropertyTypeFromGetterMethod($className, $propertyName);
            $paramTypes[$propertyName] = $type;
        }

        $requiredConstructorParams = [];

        // Check for constructor parameters
        $constructor = $realClass->getConstructor();
        if ($constructor !== null) {
            // Inject data constructor parameter
            if ($this->dtoReflection->isDataObject($realClass->getName())) {
                foreach ($data as $propertyName => $propertyValue) {
                    $type = $paramTypes[$propertyName];
                    if ($paramTypes[$propertyName] !== '') {
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName] = [
                            'type' => $type
                        ];
                    }
                }
            }

            // Inject into named parameters if a getter method exists
            $parameters = $constructor->getParameters();
            foreach ($parameters as $parameter) {
                $snakeCaseProperty = SimpleDataObjectConverter::camelCaseToSnakeCase($parameter->getName());
                $type = $paramTypes[$snakeCaseProperty] ?? '';

                if (($type !== '') && isset($data[$snakeCaseProperty])) {
                    unset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$snakeCaseProperty]);
                    $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$snakeCaseProperty] = [
                        'parameter' => $parameter->getName(),
                        'type' => $type
                    ];

                    if (!$parameter->isDefaultValueAvailable()) {
                        $requiredConstructorParams[] = $snakeCaseProperty;
                    }
                }
            }
        }

        // Fall back to setters if defined
        foreach ($data as $propertyName => $propertyValue) {
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            try {
                $setterMethod = $this->nameFinder->getSetterMethodName($class, $camelCaseProperty);
                $type = $paramTypes[$propertyName] ?? '';
                if ($type !== '') {
                    if (in_array($propertyName, $requiredConstructorParams, true)) {
                        continue;
                    }

                    unset(
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName],
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$propertyName]
                    );

                    $strategy[self::HYDRATOR_STRATEGY_SETTER][$propertyName] = [
                        'type' => $type,
                        'method' => $setterMethod
                    ];
                }

            } catch (LogicException $e) {
                if (!isset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName]) &&
                    !isset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$propertyName])
                ) {
                    $strategy[self::HYDRATOR_STRATEGY_ORPHAN][] = $propertyName;
                }
            }
        }

        return $strategy;
    }
}
