<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;

/**
 * Determines if the customer allows remote shopping assistance
 */
class isRemoteShoppingAssistanceAllowed implements ResolverInterface
{
    /**
     * @var IsAssistanceEnabled
     */
    private $isAssistanceEnabled;

    /**
     * @param IsAssistanceEnabled $isAssistanceEnabled
     */
    public function __construct(
        IsAssistanceEnabled $isAssistanceEnabled
    ) {
        $this->isAssistanceEnabled = $isAssistanceEnabled;
    }

    /**
     * Determines if remote shopping assistance is allowed for the specified customer
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return $this->isAssistanceEnabled->execute((int)$value['model']->getId());
    }
}
