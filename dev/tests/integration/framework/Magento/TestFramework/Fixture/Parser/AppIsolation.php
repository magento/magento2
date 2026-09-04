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
        $methodName = null;
        if ($scope !== ParserInterface::SCOPE_CLASS) {
            try {
                $methodName = $test->name();
            } catch (\Throwable $e) {
                return [];
            }
        }
        try {
            $reflection = $scope === ParserInterface::SCOPE_CLASS
                ? new ReflectionClass($test)
                : new ReflectionMethod($test, $methodName);
        } catch (ReflectionException $e) {
            throw new LocalizedException(
                __(
                    'Unable to parse attributes for %1',
                    get_class($test) . ($scope === ParserInterface::SCOPE_CLASS ? ' (class level)' : ' (method level)')
                ),
                $e
            );
        }

        $fixtures = [];
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
