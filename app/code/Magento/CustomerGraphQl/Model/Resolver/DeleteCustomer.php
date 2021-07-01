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
use Magento\Framework\Registry;
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

    /** @var Registry */
    private $registry;

    /**
     * @param DeleteCustomerModel $deleteCustomer
     * @param Registry $registry
     */
    public function __construct(
        DeleteCustomerModel $deleteCustomer,
        Registry $registry
    ) {
        $this->deleteCustomer = $deleteCustomer;
        $this->registry =$registry;
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

        $isSecure = $this->registry->registry('isSecureArea');

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $this->deleteCustomer->execute($context);

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', $isSecure);
        return true;
    }
}
