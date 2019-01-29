<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

class AdditionalDataBuilderPool
{
    /**
     * @var AdditionalDataBuilderInterface[]
     */
    private $builders = [];

    public function __construct(
        array $builders
    ) {
        $this->builders = $builders;
    }

    public function buildForMethod(string $methodCode, array $args): array
    {
        $additionalData = [];
        if (isset($this->builders[$methodCode])) {
            $additionalData = $this->builders[$methodCode]->build($args);
        }

        return $additionalData;
    }
}
