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
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Stdlib\Parameters;

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
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @param Transaction $transaction
     * @param ResponseValidator $responseValidator
     * @param PaymentFailuresInterface $paymentFailures
     * @param Json $json
     * @param Transparent $transparent
     * @param ArrayManager $arrayManager
     * @param GetCartForUser $getCartForUser
     * @param Parameters $parameters
     */
    public function __construct(
        Transaction $transaction,
        ResponseValidator $responseValidator,
        PaymentFailuresInterface $paymentFailures,
        Json $json,
        Transparent $transparent,
        ArrayManager $arrayManager,
        GetCartForUser $getCartForUser,
        Parameters $parameters
    ) {
        $this->transaction = $transaction;
        $this->responseValidator = $responseValidator;
        $this->paymentFailures = $paymentFailures;
        $this->json = $json;
        $this->transparent = $transparent;
        $this->arrayManager = $arrayManager;
        $this->getCartForUser = $getCartForUser;
        $this->parameters = $parameters;
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
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }

        if (!isset($args['input']['paypal_payload']) || empty($args['input']['paypal_payload'])) {
            throw new GraphQlInputException(__('Required parameter "paypal_payload" is missing.'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        $paypalPayload = $args['input']['paypal_payload'] ?? '';

        // this is a replacement for parse_str()
        $this->parameters->fromString(urldecode($paypalPayload));
        $data = $this->parameters->toArray();
        try {
            $do = new \Magento\Framework\DataObject(array_change_key_case($data, CASE_LOWER));
            $this->responseValidator->validate($do, $this->transparent);
            $this->transaction->savePaymentInQuote($do, $cart->getId());
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
