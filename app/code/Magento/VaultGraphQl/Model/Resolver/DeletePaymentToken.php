<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\VaultGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

/**
 * Delete Payment Token resolver, used for GraphQL mutation processing.
 */
class DeletePaymentToken implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param GetCustomer $getCustomer
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        GetCustomer $getCustomer,
        PaymentTokenManagementInterface $paymentTokenManagement,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->getCustomer = $getCustomer;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenRepository = $paymentTokenRepository;
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
        if (!isset($args['public_hash'])) {
            throw new GraphQlInputException(__('Specify the "public_hash" value.'));
        }

        $customer = $this->getCustomer->execute($context);

        $token = $this->paymentTokenManagement->getByPublicHash($args['public_hash'], $customer->getId());
        if (!$token) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a token using public hash: %1', $args['public_hash'])
            );
        }

        return ['result' => $this->paymentTokenRepository->delete($token)];
    }
}
