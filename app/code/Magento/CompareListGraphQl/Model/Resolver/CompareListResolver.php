<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CompareListGraphQl\Model\Resolver;


use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\CustomerData\CompareProducts;

class CompareListResolver implements ResolverInterface
{

    /**
     * @var CompareProducts
     */
    private $customerCompareList;

    public function __construct(CompareProducts $customerCompareList)
    {
        $this->customerCompareList = $customerCompareList;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $items = [];
        $test = $this->customerCompareList->getSectionData();
        $items[] = [
            'sku' => 'test123',
            'name' => (string)'NAME test',
            'productId' => (int) 123,
            'canonical_url' => 'canonical_url'
        ];
        return [
            'list_id' => $context->getUserId(),
            'items' => $items
        ];
    }
}
