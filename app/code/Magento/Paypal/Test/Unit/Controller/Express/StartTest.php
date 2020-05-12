<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Express;

use Magento\Paypal\Model\Express\Checkout;
use Magento\Paypal\Test\Unit\Controller\ExpressTest;

class StartTest extends ExpressTest
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
            ->willReturn($buttonParam);
        $this->checkout->expects($this->once())
            ->method('setIsBml')
            ->with((bool)$buttonParam);

        $this->request->expects($this->at(2))
            ->method('getParam')
            ->with(Checkout::PAYMENT_INFO_BUTTON)
            ->willReturn($buttonParam);
        $this->customerData->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->checkout->expects($this->once())
            ->method('start')
            ->with($this->anything(), $this->anything(), (bool)$buttonParam);
        $this->model->execute();
    }

    /**
     * @return array
     */
    public function startActionDataProvider()
    {
        return [['1'], [null]];
    }
}
