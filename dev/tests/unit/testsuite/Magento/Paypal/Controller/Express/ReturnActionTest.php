<?php
/**
 *
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
namespace Magento\Paypal\Controller\Express;

class ReturnActionTest extends \Magento\Paypal\Controller\ExpressTest
{
    protected $name = 'ReturnAction';

    /**
     * @param string $path
     */
    protected function _expectRedirect($path = '*/*/review')
    {
        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), $path, []);
    }

    public function testExecuteAuthorizationRetrial()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('retry_authorization')
            ->will($this->returnValue('true'));
        $this->checkoutSession->expects($this->once())
            ->method('__call')
            ->with('getPaypalTransactionData')
            ->will($this->returnValue(['any array']));
        $this->_expectForwardPlaceOrder();
        $this->model->execute();
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @param bool $canSkipOrderReviewStep
     * @dataProvider trueFalseDataProvider
     */
    public function testExecute($canSkipOrderReviewStep)
    {
        $this->checkoutSession->expects($this->at(0))
            ->method('__call')
            ->with('unsPaypalTransactionData');
        $this->checkout->expects($this->once())
            ->method('canSkipOrderReviewStep')
            ->will($this->returnValue($canSkipOrderReviewStep));
        if ($canSkipOrderReviewStep) {
            $this->_expectForwardPlaceOrder();
        } else {
            $this->_expectRedirect();
        }
        $this->model->execute();
    }

    private function _expectForwardPlaceOrder()
    {
        $this->request->expects($this->once())
            ->method('setActionName')
            ->with('placeOrder');
        $this->request->expects($this->once())
            ->method('setDispatched')
            ->with(false);
    }
}
