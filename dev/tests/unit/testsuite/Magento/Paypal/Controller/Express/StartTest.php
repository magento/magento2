<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Express;

class StartTest extends \Magento\Paypal\Controller\ExpressTest
{
    protected $name = 'Start';

    /**
     * @param null|bool $buttonParam
     * @dataProvider startActionDataProvider
     */
    public function testStartAction($buttonParam)
    {
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('bml')
            ->will($this->returnValue($buttonParam));
        $this->checkout->expects($this->once())
            ->method('setIsBml')
            ->with((bool)$buttonParam);

        $this->request->expects($this->at(2))
            ->method('getParam')
            ->with(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON)
            ->will($this->returnValue($buttonParam));
        $this->customerData->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->checkout->expects($this->once())
            ->method('start')
            ->with($this->anything(), $this->anything(), (bool)$buttonParam);
        $this->model->execute();
    }

    public function startActionDataProvider()
    {
        return [['1'], [null]];
    }
}
