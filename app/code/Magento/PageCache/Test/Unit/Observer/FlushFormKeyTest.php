<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\App\PageCache\FormKey as CookieFormKey;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
use Magento\Framework\Event\Observer;
use Magento\PageCache\Observer\FlushFormKey;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlushFormKeyTest extends TestCase
{
    /**
     * Test case for deleting the form_key cookie when observer executes
     */
    public function testExecute()
    {
        /** @var CookieFormKey|MockObject $cookieFormKey */
        $cookieFormKey = $this->getMockBuilder(CookieFormKey::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var DataFormKey|MockObject $dataFormKey */
        $dataFormKey = $this->getMockBuilder(DataFormKey::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Observer|MockObject $observerObject */
        $observerObject = $this->createMock(Observer::class);
        $observer = new FlushFormKey($cookieFormKey, $dataFormKey);

        $cookieFormKey->expects($this->once())
            ->method('delete');
        $dataFormKey->expects($this->once())
            ->method('set')
            ->with(null);
        $observer->execute($observerObject);
    }
}
