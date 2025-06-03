<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Stdlib\Parameters;
use Magento\Framework\DataObjectFactory;

/**
 * Resolver for handling PayflowPro response
 */
class PayflowProResponse implements ResolverInterface
{

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @var PaymentFailuresInterface
     */
    private $paymentFailures;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * PayflowProResponse constructor.
     * @param Transaction $transaction
     * @param ResponseValidator $responseValidator
     * @param PaymentFailuresInterface $paymentFailures
     * @param Json $json
     * @param Transparent $transparent
     * @param GetCartForUser $getCartForUser
     * @param Parameters $parameters
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Transaction $transaction,
        ResponseValidator $responseValidator,
        PaymentFailuresInterface $paymentFailures,
        Json $json,
        Transparent $transparent,
        GetCartForUser $getCartForUser,
        Parameters $parameters,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->transaction = $transaction;
        $this->responseValidator = $responseValidator;
        $this->paymentFailures = $paymentFailures;
        $this->json = $json;
        $this->transparent = $transparent;
        $this->getCartForUser = $getCartForUser;
        $this->parameters = $parameters;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }

        if (!isset($args['input']['paypal_payload']) || empty($args['input']['paypal_payload'])) {
            throw new GraphQlInputException(__('Required parameter "paypal_payload" is missing.'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $paypalPayload = $args['input']['paypal_payload'] ?? '';

        // this is a replacement for parse_str()
        $this->parameters->fromString(urldecode($paypalPayload));
        $data = $this->parameters->toArray();
        try {
            $response = $this->transaction->getResponseObject($data);
            $this->responseValidator->validate($response, $this->transparent);
            $this->transaction->savePaymentInQuote($response, $cart->getId());
        } catch (LocalizedException $exception) {
            $parameters['error'] = true;
            $parameters['error_msg'] = $exception->getMessage();
            $this->paymentFailures->handle((int) $cart->getId(), $parameters['error_msg']);
            throw new GraphQlInputException(__($exception->getMessage()));
        }
        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
