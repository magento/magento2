<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Parser;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * AppIsolation attribute parser
 */
class AppIsolation implements ParserInterface
{
    /**
     * @var string
     */
    private string $attributeClass;

    /**
     * @param string $attributeClass
     */
    public function __construct(
        string $attributeClass = \Magento\TestFramework\Fixture\AppIsolation::class
    ) {
        $this->attributeClass = $attributeClass;
    }

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        $fixtures = [];
        try {
            if ($scope === ParserInterface::SCOPE_CLASS) {
                $reflection = new ReflectionClass($test);
            } else {
                // Check if name property is initialized before accessing
                // The 'name' property is from PHPUnit\Framework\TestCase
                $nameReflection = new ReflectionClass(TestCase::class);
                $nameProperty = $nameReflection->getProperty('name');
                
                if (!$nameProperty->isInitialized($test)) {
                    // Cannot parse method-level attributes without a test name
                    return [];
                }
                
                $reflection = new ReflectionMethod($test, $test->name());
            }
        } catch (ReflectionException $e) {
            $context = $scope === ParserInterface::SCOPE_CLASS ? ' (class level)' : ' (method level)';
            throw new LocalizedException(
                __('Unable to parse attributes for %1', get_class($test) . $context),
                $e
            );
        }

        $attributes = $reflection->getAttributes($this->attributeClass);

        foreach ($attributes as $attribute) {
            $args = $attribute->getArguments();
            $fixtures[] = [
                'enabled' => $args[0],
            ];
        }
        return $fixtures;
    }
}
