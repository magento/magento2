<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace TestDeferred;

/**
 * Class meant for testing deferred proxy.
 */
class TestClass
{
    /**
     * Number of instances created.
     *
     * @var int
     */
    public static $created = 0;

    /**
     * @var bool
     */
    private $cloned = false;

    /**
     * @var string
     */
    private $value;

    /**
     * TestClassForProxy constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
        self::$created++;
    }

    /**
     * Initial value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Whether the object is a clone.
     *
     * @return bool
     */
    public function isCloned(): bool
    {
        return $this->cloned;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->cloned = true;
    }
}
