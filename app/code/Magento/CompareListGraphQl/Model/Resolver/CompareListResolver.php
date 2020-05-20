<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CompareListGraphQl\Model\Resolver;


use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\CustomerData\CompareProducts;
use Magento\GraphQl\Model\Query\ContextInterface;

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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        return ['list_id' => $context->getUserId()];
    }
}
