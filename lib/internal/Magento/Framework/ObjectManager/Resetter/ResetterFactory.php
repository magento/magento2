<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Resetter;

/**
 * Factory that creates Resetter based on environment variable.
 */
class ResetterFactory
{
    /**
     * @var string
     */
    private static string $resetterClassName = Resetter::class;

    /**
     * Create resseter factory
     *
     * @return ResetterInterface
     * @phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function create() : ResetterInterface
    {
        return new static::$resetterClassName;
    }

    /**
     * Sets resetter class name
     *
     * @param string $resetterClassName
     * @return void
     * @phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function setResetterClassName($resetterClassName) : void
    {
        static::$resetterClassName = $resetterClassName;
    }
}
