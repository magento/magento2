<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Cleanup\TestCasePropertiesTest;

/**
 * Fixture with typed and readonly properties for suite-end cleanup.
 */
class TypedDummyTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \stdClass
     */
    private \stdClass $typed;

    /**
     * Assigned in testDummy(). TestCase::__construct() is final, and a new-expression
     * property default is a fatal error on PHP 8.5.
     *
     * @var \stdClass
     * @phpstan-ignore property.uninitializedReadonly
     */
    private readonly \stdClass $fixed;

    /**
     * @var string|null
     */
    private ?string $nullable = 'set';

    /**
     * @var string
     */
    public $untyped = 'set';

    /**
     * @var array
     */
    private array $items = ['set'];

    /**
     * @var array
     */
    private array $bareItems;

    /**
     * @return void
     */
    public function testDummy(): void
    {
        $this->typed = new \stdClass();
        $this->fixed = new \stdClass();
        $this->bareItems = ['set'];
    }

    /**
     * @return string|null
     */
    public function getNullable(): ?string
    {
        return $this->nullable;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
