<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProcessableExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Paypal\Model\Api\ProcessableException
     */
    protected $model;

    /**
     * @param int $code
     * @param string $msg
     * @return void
     * @dataProvider getUserMessageDataProvider
     */
    public function testGetUserMessage($code, $msg)
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = new \Magento\Paypal\Model\Api\ProcessableException(__($msg), null, $code);
        $this->assertEquals($msg, $this->model->getUserMessage());
    }

    /**
     * @return array
     */
    public function getUserMessageDataProvider()
    {
        return [
            [
                10001,
                "I'm sorry - but we were not able to process your payment. "
                . "Please try another payment method or contact us so we can assist you.",
            ],
            [
                10417,
                "I'm sorry - but we were not able to process your payment. "
                . "Please try another payment method or contact us so we can assist you."
            ],
            [
                10537,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                10538,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                10539,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [10411, "something went wrong"]
        ];
    }
}
