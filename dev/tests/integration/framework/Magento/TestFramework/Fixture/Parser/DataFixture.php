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

        $fixtures = [];
        $attributes = $reflection->getAttributes($this->attributeClass);
        foreach ($attributes as $attribute) {
            /** @var \Magento\TestFramework\Fixture\DataFixture $dataFixture */
            $dataFixture = $attribute->newInstance();
            $alias = $dataFixture->as;
            $count = $dataFixture->count;
            $id = $count > 1 ? 1 : '';
            do {
                $fixtures[] = [
                    'name' => $alias !== null ? $alias.(!empty($id) ? $id++ : '') : null,
                    'factory' => $dataFixture->type,
                    'data' => $dataFixture->data,
                    'scope' => $dataFixture->scope,
                    'scopeType' => $dataFixture->scopeType,
                ];
            } while (--$count > 0);

        }
        return $fixtures;
    }
}
