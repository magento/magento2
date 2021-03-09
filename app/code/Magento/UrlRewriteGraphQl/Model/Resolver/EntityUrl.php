<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * UrlRewrite field resolver, used for GraphQL request processing.
 */
class EntityUrl extends AbstractEntityUrl implements ResolverInterface
{
}
