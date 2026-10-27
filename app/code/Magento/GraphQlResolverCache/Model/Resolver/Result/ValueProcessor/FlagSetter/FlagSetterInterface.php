<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagSetter;

/**
 * Sets a value processing flag on value and unsets flag from value.
 */
interface FlagSetterInterface
{
    /**
     * Set the value processing flag on value.
     *
     * @param array $value
     * @param string $flagValue
     * @return void
     */
    public function setFlagOnValue(&$value, string $flagValue): void;

    /**
     * Unsets flag from value.
     *
     * @param array $value
     * @return void
     */
    public function unsetFlagFromValue(&$value): void;
}
