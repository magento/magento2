<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Model\Plugin\CustomerFlushFormKey;
use Magento\Customer\Model\Session;
use Magento\Framework\App\PageCache\FormKey as CookieFormKey;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
use Magento\Framework\Event\Observer;
use Magento\PageCache\Observer\FlushFormKey;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerFlushFormKeyTest extends TestCase
{
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

    protected function setUp(): void
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
            ->addMethods(['getBeforeRequestParams', 'setBeforeRequestParams'])
            ->getMock();
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
        $observerDto = new Observer();
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

        $proceed = function ($observerDto) use ($observer) {
            return $observer->execute($observerDto);
        };

        $plugin->aroundExecute($observer, $proceed, $observerDto);
    }

    /**
     * Data provider for testAroundFlushFormKey
     *
     * @return array
     */
    public static function aroundFlushFormKeyProvider()
    {
        return [
            ['form_key_value', 'form_key_value', 2, 1],
            ['form_old_key_value', 'form_key_value', 1, 0],
            [null, 'form_key_value', 1, 0]
        ];
    }
}
