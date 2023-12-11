<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
