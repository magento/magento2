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
 * DataFixture attribute parser
 */
class DataFixture implements ParserInterface
{
    /**
     * @var string
     */
    private string $attributeClass;

    /**
     * @param string $attributeClass
     */
    public function __construct(
        string $attributeClass = \Magento\TestFramework\Fixture\DataFixture::class
    ) {
        $this->attributeClass = $attributeClass;
    }

    /**
     * @inheritdoc
     */
    public function parse(TestCase $test, string $scope): array
    {
        try {
            $reflection = $scope === ParserInterface::SCOPE_CLASS
                ? new \ReflectionClass($test)
                : new \ReflectionMethod($test, $test->getName(false));
        } catch (\ReflectionException $e) {
            throw new LocalizedException(
                __(
                    'Unable to parse attributes for %1',
                    get_class($test) . ($scope === ParserInterface::SCOPE_CLASS ? '' : '::' . $test->getName(false))
                ),
                $e
            );
        }

        $fixtures = [];
        $attributes = $reflection->getAttributes($this->attributeClass);
        foreach ($attributes as $attribute) {
            $args = $attribute->getArguments();
            $alias = $args['as'] ?? $args[2] ?? null;
            $count = $args['count'] ?? $args[4] ?? 1;
            $id = $count > 1 ? 1 : '';
            do {
                $fixtures[] = [
                    'name' => $alias !== null ? $alias.(!empty($id) ? $id++ : '') : null,
                    'factory' => $args[0],
                    'data' => $args[1] ?? [],
                    'scope' => $args['scope'] ?? $args[3] ?? null,
                ];
            } while (--$count > 0);

        }
        return $fixtures;
    }
}
