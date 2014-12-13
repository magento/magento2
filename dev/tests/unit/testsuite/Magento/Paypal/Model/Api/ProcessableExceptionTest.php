<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Paypal\Model\Api;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ProcessableExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Paypal\Model\Api\ProcessableException */
    protected $model;

    /**
     * @dataProvider getUserMessageDataProvider
     */
    public function testGetUserMessage($code, $msg)
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Paypal\Model\Api\ProcessableException',
            ['message' => $msg, 'code' => $code]
        );
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
