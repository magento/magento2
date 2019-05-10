<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

class PaymentTokenTypeResolver implements TypeResolverInterface
{

    public function resolveType(array $data): string
    {
        //TODO
        return 'PaypalExpressToken';
    }
}