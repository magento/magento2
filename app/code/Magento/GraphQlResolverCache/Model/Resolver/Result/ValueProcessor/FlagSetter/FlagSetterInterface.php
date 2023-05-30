<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagSetter;

interface FlagSetterInterface
{
    public function setFlagOnValue(&$value, string $flagValue): void;

    public function unsetFlagFromValue(&$value): void;
}
