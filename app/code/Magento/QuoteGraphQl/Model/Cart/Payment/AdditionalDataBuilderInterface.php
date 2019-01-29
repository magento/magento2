<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

interface AdditionalDataBuilderInterface
{
    public function build(array $args): array;
}
