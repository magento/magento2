<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\Parameters;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\PaypalGraphQl\Model\Resolver\PayflowProResponse;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Covers the requirement that the PayflowPro response resolver only notifies the merchant of a
 * payment failure for a cart that is in a genuine Payflow payment context.
 */
#[AllowMockObjectsWithoutExpectations]
class PayflowProResponseTest extends TestCase
{
    use MockCreationTrait;

    private const MASKED_CART_ID = 'masked_cart_id';
    private const CART_ID = 100;

    /**
     * @var PayflowProResponse
     */
    private PayflowProResponse $resolver;

    /** @var Transaction|MockObject */
    private $transaction;

    /** @var ResponseValidator|MockObject */
    private $responseValidator;

    /** @var PaymentFailuresInterface|MockObject */
    private $paymentFailures;

    /** @var GetCartForUser|MockObject */
    private $getCartForUser;

    /** @var Parameters|MockObject */
    private $parameters;

    /** @var Field|MockObject */
    private $field;

    /** @var ResolveInfo|MockObject */
    private $info;

    /** @var ContextInterface|MockObject */
    private $context;

    /** @var Quote|MockObject */
    private $cart;

    /** @var Payment|MockObject */
    private $payment;

    protected function setUp(): void
    {
        $this->transaction = $this->createMock(Transaction::class);
        $this->responseValidator = $this->createMock(ResponseValidator::class);
        $this->paymentFailures = $this->createMock(PaymentFailuresInterface::class);
        $json = $this->createMock(Json::class);
        $transparent = $this->createMock(Transparent::class);
        $this->getCartForUser = $this->createMock(GetCartForUser::class);
        $this->parameters = $this->createMock(Parameters::class);
        $dataObjectFactory = $this->createMock(DataObjectFactory::class);

        $this->resolver = new PayflowProResponse(
            $this->transaction,
            $this->responseValidator,
            $this->paymentFailures,
            $json,
            $transparent,
            $this->getCartForUser,
            $this->parameters,
            $dataObjectFactory
        );

        $this->field = $this->createMock(Field::class);
        $this->info = $this->createMock(ResolveInfo::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $extensionAttributes = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getStore']
        );
        $extensionAttributes->method('getStore')->willReturn($store);
        $this->context = $this->createMock(ContextInterface::class);
        $this->context->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->context->method('getUserId')->willReturn(null);

        $this->payment = $this->createMock(Payment::class);
        $this->cart = $this->createMock(Quote::class);
        $this->cart->method('getId')->willReturn(self::CART_ID);
        $this->cart->method('getPayment')->willReturn($this->payment);
        $this->getCartForUser->method('execute')->willReturn($this->cart);
    }

    /**
     * A cart with no Payflow method selected never entered a real payment attempt, so the resolver
     * must refuse it before it can reach the merchant payment-failure notification.
     *
     * @param string|null $method
     */
    #[DataProvider('nonPayflowMethodProvider')]
    public function testCartWithoutPayflowMethodIsRefusedWithoutNotifying($method): void
    {
        $this->payment->method('getMethod')->willReturn($method);

        $this->paymentFailures->expects($this->never())->method('handle');
        $this->responseValidator->expects($this->never())->method('validate');
        $this->transaction->expects($this->never())->method('getResponseObject');

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Transaction has been declined.');

        $this->resolver->resolve(
            $this->field,
            $this->context,
            $this->info,
            null,
            ['input' => ['cart_id' => self::MASKED_CART_ID, 'paypal_payload' => 'RESULT=12&RESPMSG=Declined']]
        );
    }

    /**
     * @return array<string, array<int, string|null>>
     */
    public static function nonPayflowMethodProvider(): array
    {
        return [
            'no method selected' => [null],
            'empty method' => [''],
            'unrelated method' => ['checkmo'],
        ];
    }

    /**
     * A genuine Payflow decline still carries a Payflow method, so the merchant notification must
     * still fire for it. This is the behavior the guard preserves.
     *
     * @param string $method
     */
    #[DataProvider('payflowMethodProvider')]
    public function testGenuinePayflowDeclineStillNotifies($method): void
    {
        $this->payment->method('getMethod')->willReturn($method);
        $this->parameters->method('toArray')->willReturn(['RESULT' => '12', 'RESPMSG' => 'Declined']);
        $this->transaction->method('getResponseObject')->willReturn($this->createMock(DataObject::class));
        $this->responseValidator->method('validate')
            ->willThrowException(new LocalizedException(__('Payflow gateway declined the transaction.')));

        $this->paymentFailures->expects($this->once())
            ->method('handle')
            ->with(self::CART_ID, 'Payflow gateway declined the transaction.');

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Payflow gateway declined the transaction.');

        $this->resolver->resolve(
            $this->field,
            $this->context,
            $this->info,
            null,
            ['input' => ['cart_id' => self::MASKED_CART_ID, 'paypal_payload' => 'RESULT=12&RESPMSG=Declined']]
        );
    }

    /**
     * A valid Payflow response is processed and the cart is returned, with no notification sent.
     *
     * @param string $method
     */
    #[DataProvider('payflowMethodProvider')]
    public function testValidPayflowResponseReturnsCart($method): void
    {
        $this->payment->method('getMethod')->willReturn($method);
        $this->parameters->method('toArray')->willReturn(['RESULT' => '0', 'RESPMSG' => 'Approved']);
        $this->transaction->method('getResponseObject')->willReturn($this->createMock(DataObject::class));
        $this->responseValidator->method('validate');

        $this->paymentFailures->expects($this->never())->method('handle');

        $result = $this->resolver->resolve(
            $this->field,
            $this->context,
            $this->info,
            null,
            ['input' => ['cart_id' => self::MASKED_CART_ID, 'paypal_payload' => 'RESULT=0&RESPMSG=Approved']]
        );

        $this->assertSame(['cart' => ['model' => $this->cart]], $result);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function payflowMethodProvider(): array
    {
        return [
            'payflowpro' => [Config::METHOD_PAYFLOWPRO],
            'payflowpro cc vault' => [Transparent::CC_VAULT_CODE],
        ];
    }
}
