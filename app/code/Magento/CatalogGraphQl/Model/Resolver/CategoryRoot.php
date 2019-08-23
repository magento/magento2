<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Category\GetRootCategoryId;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Category tree field resolver, used for GraphQL request processing.
 */
class CategoryRoot implements ResolverInterface
{
    /**
     * @var GetRootCategoryId
     */
    private $getRootCategoryId;

    /**
     * @param GetRootCategoryId $getRootCategoryId
     */
    public function __construct(
        GetRootCategoryId $getRootCategoryId
    ) {
        $this->getRootCategoryId = $getRootCategoryId;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->getRootCategoryId->execute();
    }
}
