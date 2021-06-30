<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\DeleteCustomer as DeleteCustomerModel;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Delete customer, used for GraphQL request processing.
 */
class DeleteCustomer implements ResolverInterface
{
    /**
     * @var DeleteCustomerModel
     */
    private $deleteCustomer;

    /**
     * @param DeleteCustomerModel $deleteCustomer
     */
    public function __construct(
        DeleteCustomerModel $deleteCustomer
    ) {
        $this->deleteCustomer = $deleteCustomer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $this->deleteCustomer->execute($context);
        return true;
    }
}
