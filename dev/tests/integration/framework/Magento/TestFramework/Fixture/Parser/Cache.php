<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Parser;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

/**
 * Cache attribute parser
 */
class Cache implements ParserInterface
{
    /**
     * @var string
     */
    private string $attributeClass;

    /**
     * @param string $attributeClass
     */
    public function __construct(
        string $attributeClass = \Magento\TestFramework\Fixture\Cache::class
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
            $reflection = $scope === ParserInterface::SCOPE_CLASS
                ? new \ReflectionClass($test)
                : new \ReflectionMethod($test, $test->name());
        } catch (\ReflectionException $e) {
            throw new LocalizedException(
                __(
                    'Unable to parse attributes for %1',
                    get_class($test) . ($scope === ParserInterface::SCOPE_CLASS ? '' : '::' . $test->name())
                ),
                $e
            );
        }

        $attributes = $reflection->getAttributes($this->attributeClass);
        foreach ($attributes as $attribute) {
            $args = $attribute->getArguments();
            $fixtures[] = [
                'type' => $args[0],
                'status' => $args[1]
            ];
        }
        return $fixtures;
    }
}
