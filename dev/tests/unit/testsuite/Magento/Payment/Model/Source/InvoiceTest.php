<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Source;

class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Invoice
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Invoice();
    }

    public function testToOptionArray()
    {
        $expectedResult = [
            [
                'value' => \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Yes'),
            ],
            ['value' => '', 'label' => __('No')],
        ];

        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
