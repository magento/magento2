<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Stub data builder.
 *
 * Since the order of params is matters for Authorize.net request,
 * this builder is used to reserve a place in builders sequence.
 */
class StubDataBuilder implements BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        return [];
    }
}
