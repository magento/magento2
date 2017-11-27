<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Model\Plugin\CustomerFlushFormKey;
use Magento\Customer\Model\Session;
use Magento\Framework\App\PageCache\FormKey as CookieFormKey;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
use Magento\PageCache\Observer\FlushFormKey;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CustomerFlushFormKeyTest extends TestCase
{
    const CLOSURE_VALUE = 'CLOSURE';

    /**
     * @var CookieFormKey | MockObject
     */
    private $cookieFormKey;

    /**
     * @var Session | MockObject
     */
    private $customerSession;

    /**
     * @var DataFormKey | MockObject
     */
    private $dataFormKey;

    /**
     * @var \Closure
     */
    private $closure;

    protected function setUp()
    {

        /** @var CookieFormKey | MockObject */
        $this->cookieFormKey = $this->getMockBuilder(CookieFormKey::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataFormKey | MockObject */
        $this->dataFormKey = $this->getMockBuilder(DataFormKey::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Session | MockObject */
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBeforeRequestParams', 'setBeforeRequestParams'])
            ->getMock();

        $this->closure = function () {
            return static::CLOSURE_VALUE;
        };
    }

    /**
     * @dataProvider aroundFlushFormKeyProvider
     * @param $beforeFormKey
     * @param $currentFormKey
     * @param $getFormKeyTimes
     * @param $setBeforeParamsTimes
     */
    public function testAroundFlushFormKey(
        $beforeFormKey,
        $currentFormKey,
        $getFormKeyTimes,
        $setBeforeParamsTimes
    ) {
        $observer = new FlushFormKey($this->cookieFormKey, $this->dataFormKey);
        $plugin = new CustomerFlushFormKey($this->customerSession, $this->dataFormKey);

        $beforeParams['form_key'] = $beforeFormKey;

        $this->dataFormKey->expects($this->exactly($getFormKeyTimes))
            ->method('getFormKey')
            ->willReturn($currentFormKey);

        $this->customerSession->expects($this->once())
            ->method('getBeforeRequestParams')
            ->willReturn($beforeParams);

        $this->customerSession->expects($this->exactly($setBeforeParamsTimes))
            ->method('setBeforeRequestParams')
            ->with($beforeParams);

        $plugin->aroundExecute($observer, $this->closure, $observer);
    }

    /**
     * Data provider for testAroundFlushFormKey
     *
     * @return array
     */
    public function aroundFlushFormKeyProvider()
    {
        return [
            ['form_key_value', 'form_key_value', 2, 1],
            ['form_old_key_value', 'form_key_value', 1, 0],
            [null, 'form_key_value', 1, 0]
        ];
    }
}
