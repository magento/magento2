<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Report;

use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use DateTime;
use Magento\Braintree\Model\Report\Row\TransactionMap;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TransactionMapTest
 *
 * Test for class \Magento\Braintree\Model\Report\\Row\TransactionMap
 */
class TransactionMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionStub;

    /**
     * @var AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeValueFactoryMock;

    /**
     * @var RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultRenderer;

    /**
     * @var RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rendererMock;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->defaultRenderer = Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder(RendererInterface::class)
            ->getMock();
    }

    /**
     * Get items
     *
     * @param array $transaction
     * @dataProvider getConfigDataProvider
     */
    public function testGetCustomAttributes($transaction)
    {
        $this->transactionStub = Transaction::factory($transaction);

        $fields = TransactionMap::$simpleFieldsMap;
        $fieldsQty = count($fields);

        $this->attributeValueFactoryMock->expects($this->exactly($fieldsQty))
            ->method('create')
            ->willReturnCallback(function () {
                return new AttributeValue();
            });

        $map = new TransactionMap(
            $this->attributeValueFactoryMock,
            $this->transactionStub
        );

        Phrase::setRenderer($this->rendererMock);

        /** @var AttributeValue[] $result */
        $result = $map->getCustomAttributes();

        $this->assertSame($fieldsQty, count($result));
        $this->assertInstanceOf(AttributeValue::class, $result[1]);
        $this->assertSame($transaction['id'], $result[0]->getValue());
        $this->assertSame($transaction['paypalDetails']->paymentId, $result[4]->getValue());
        $this->assertSame(
            $transaction['createdAt']->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            $result[6]->getValue()
        );
        $this->assertSame(implode(', ', $transaction['refundIds']), $result[11]->getValue());
        $this->assertSame($transaction['merchantAccountId'], $result[1]->getValue());
        $this->assertSame($transaction['orderId'], $result[2]->getValue());
        $this->assertSame($transaction['amount'], $result[7]->getValue());
        $this->assertSame($transaction['processorSettlementResponseCode'], $result[8]->getValue());
        $this->assertSame($transaction['processorSettlementResponseText'], $result[10]->getValue());
        $this->assertSame($transaction['settlementBatchId'], $result[12]->getValue());
        $this->assertSame($transaction['currencyIsoCode'], $result[13]->getValue());

        $this->rendererMock->expects($this->at(0))
            ->method('render')
            ->with([$transaction['paymentInstrumentType']])
            ->willReturn('Credit card');
        $this->assertSame('Credit card', $result[3]->getValue()->render());

        $this->rendererMock->expects($this->at(0))
            ->method('render')
            ->with([$transaction['type']])
            ->willReturn('Sale');
        $this->assertSame('Sale', $result[5]->getValue()->render());

        $this->rendererMock->expects($this->at(0))
            ->method('render')
            ->with([$transaction['status']])
            ->willReturn('Pending for settlement');
        $this->assertSame('Pending for settlement', $result[9]->getValue()->render());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'transaction' => [
                    'id' => 1,
                    'createdAt' => new \DateTime(),
                    'paypalDetails' => new PayPalDetails(['paymentId' => 10]),
                    'refundIds' => [1, 2, 3, 4, 5],
                    'merchantAccountId' => 'MerchantId',
                    'orderId' => 1,
                    'paymentInstrumentType' => 'credit_card',
                    'type' => 'sale',
                    'amount' => '$19.99',
                    'processorSettlementResponseCode' => 1,
                    'status' => 'pending_for_settlement',
                    'processorSettlementResponseText' => 'sample text',
                    'settlementBatchId' => 2,
                    'currencyIsoCode' => 'USD'
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        Phrase::setRenderer($this->defaultRenderer);
    }
}
