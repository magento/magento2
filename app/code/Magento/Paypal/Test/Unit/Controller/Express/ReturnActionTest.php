<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Express;

use Magento\Paypal\Test\Unit\Controller\ExpressTestCase;

class ReturnActionTest extends ExpressTestCase
{
    protected $name = 'ReturnAction';

    /**
     * @param string $path
     *
     * @return void
     */
    protected function expectRedirect($path = '*/*/review'): void
    {
        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), $path, []);
    }

    /**
     * @return void
     */
    public function testExecuteAuthorizationRetrial(): void
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('retry_authorization')
            ->willReturn('true');
        $this->checkoutSession->expects($this->once())
            ->method('__call')
            ->with('getPaypalTransactionData')
            ->willReturn(['any array']);
        $this->expectForwardPlaceOrder();
        $this->model->execute();
    }

    /**
     * @return array
     */
    public static function trueFalseDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * @param bool $canSkipOrderReviewStep
     *
     * @return void
     * @dataProvider trueFalseDataProvider
     */
    public function testExecute($canSkipOrderReviewStep): void
    {
        $this->checkoutSession->method('__call')
            ->with('unsPaypalTransactionData');
        $this->checkout->expects($this->once())
            ->method('canSkipOrderReviewStep')
            ->willReturn($canSkipOrderReviewStep);
        if ($canSkipOrderReviewStep) {
            $this->expectForwardPlaceOrder();
        } else {
            $this->expectRedirect();
        }
        $this->model->execute();
    }

    /**
     * @return void
     */
    private function expectForwardPlaceOrder(): void
    {
        $this->request->expects($this->once())
            ->method('setActionName')
            ->with('placeOrder');
        $this->request->expects($this->once())
            ->method('setDispatched')
            ->with(false);
    }
}
