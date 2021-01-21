<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model\Report\Settlement;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Model\Report\Settlement\Row
     */
    protected $row;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->row = $objectManagerHelper->getObject(\Magento\Paypal\Model\Report\Settlement\Row::class);
    }

    /**
     * @param string $code
     * @param string $expectation
     * @dataProvider getReferenceTypeDataProvider
     */
    public function testGetReferenceType($code, $expectation)
    {
        $this->assertEquals($expectation, $this->row->getReferenceType($code));
    }

    /**
     * @param string $code
     * @param string $expectation
     * @dataProvider getTransactionEventDataProvider
     */
    public function testGetTransactionEvent($code, $expectation)
    {
        $this->assertEquals($expectation, $this->row->getTransactionEvent($code));
    }

    /**
     * @param string $code
     * @param string $expectation
     * @dataProvider getDebitCreditTextDataProvider
     */
    public function testGetDebitCreditText($code, $expectation)
    {
        $this->assertEquals($expectation, $this->row->getDebitCreditText($code));
    }

    /**
     * @param string $code
     * @param array $modelData
     * @param int $expectation
     * @dataProvider getCastedAmountDataProvider
     */
    public function testGetCastedAmount($code, $modelData, $expectation)
    {
        $this->row->setData($modelData);
        $this->assertEquals($expectation, $this->row->getCastedAmount($code));
    }

    public function testGetTransactionEvents()
    {
        $this->assertArrayHasKey('T1502', $this->row->getTransactionEvents());
    }

    /**
     * @return array
     */
    public function getReferenceTypeDataProvider()
    {
        return [
            ['ODR', __('Order ID')],
            ['EX_VALUE', 'EX_VALUE']
        ];
    }

    /**
     * @return array
     */
    public function getTransactionEventDataProvider()
    {
        return [
            ['T1502', __('ACH Deposit (Hold for Dispute or Other Investigation)')],
            ['EX_VALUE', 'EX_VALUE']
        ];
    }

    /**
     * @return array
     */
    public function getDebitCreditTextDataProvider()
    {
        return [
            ['CR', __('Credit')],
            ['EX_VALUE', 'EX_VALUE']
        ];
    }

    /**
     * @return array
     */
    public function getCastedAmountDataProvider()
    {
        return [
            ['fee_amount', ['fee_amount' => 1, 'fee_debit_or_credit' => 'CR'], -1],
            ['fee_amount', ['fee_amount' => 1, 'fee_debit_or_credit' => 'DB'], 1]
        ];
    }
}
