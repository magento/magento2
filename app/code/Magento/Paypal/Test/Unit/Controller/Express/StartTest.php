<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Express;

use Magento\Paypal\Model\Express\Checkout;
use Magento\Paypal\Test\Unit\Controller\ExpressTestCase;

class StartTest extends ExpressTestCase
{
    /**
     * @var string
     */
    protected $name = 'Start';

    /**
     * @param null|bool $buttonParam
     *
     * @return void
     * @dataProvider startActionDataProvider
     */
    public function testStartAction($buttonParam): void
    {
        $this->checkout->expects($this->once())
            ->method('setIsBml')
            ->with((bool)$buttonParam);

        $this->request->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['bml'] => $buttonParam,
                [Checkout::PAYMENT_INFO_BUTTON] => $buttonParam
            });
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
    public static function startActionDataProvider(): array
    {
        return [['1'], [null]];
    }
}
