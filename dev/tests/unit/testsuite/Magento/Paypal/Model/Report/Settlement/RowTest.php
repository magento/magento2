<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Paypal\Model\Report\Settlement;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Report\Settlement\Row
     */
    protected $row;


    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->row = $objectManagerHelper->getObject('Magento\Paypal\Model\Report\Settlement\Row');
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
            ['fee_amount', ['fee_amount' => 100, 'fee_debit_or_credit' => 'CR'], -1],
            ['fee_amount', ['fee_amount' => 100, 'fee_debit_or_credit' => 'DB'], 1]
        ];
    }
}
