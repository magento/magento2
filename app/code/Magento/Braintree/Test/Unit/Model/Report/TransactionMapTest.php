<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Report;

use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use DateTime;
use Magento\Braintree\Model\Report\Row\TransactionMap;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TransactionMapTest
 *
 * Test for class \Magento\Braintree\Model\Report\\Row\TransactionMap
 */
class TransactionMapTest extends \PHPUnit_Framework_TestCase
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
     * Setup
     */
    protected function setUp()
    {
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
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

        /** @var AttributeValue[] $result */
        $result = $map->getCustomAttributes();

        $this->assertEquals($fieldsQty, count($result));
        $this->assertInstanceOf(AttributeValue::class, $result[1]);
        $this->assertEquals($transaction['id'], $result[0]->getValue());
        $this->assertEquals($transaction['paypalDetails']->paymentId, $result[4]->getValue());
        $this->assertEquals(
            $transaction['createdAt']->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            $result[6]->getValue()
        );
        $this->assertEquals(implode(', ', $transaction['refundIds']), $result[11]->getValue());
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
                    'refundIds' => [1, 2, 3, 4, 5]
                ]
            ]
        ];
    }
}
