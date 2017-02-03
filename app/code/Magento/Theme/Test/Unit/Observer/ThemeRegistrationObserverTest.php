<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Observer;

class ThemeRegistrationObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Theme\Model\Theme\Registration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registration;

    /**
     * @var \Magento\Theme\Observer\ThemeRegistrationObserver
     */
    protected $themeObserver;

    protected function setUp()
    {
        $this->registration = $this->getMockBuilder('Magento\Theme\Model\Theme\Registration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            'Magento\Theme\Observer\ThemeRegistrationObserver',
            [
                'registration' => $this->registration,
                'logger' => $this->logger,
            ]
        );
    }

    public function testThemeRegistration()
    {
        $pattern = 'some pattern';
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getPathPattern'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->any())->method('getPathPattern')->willReturn($pattern);
        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $this->registration->expects($this->once())
            ->method('register')
            ->with($pattern)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('exception')));
        $this->logger->expects($this->once())
            ->method('critical');

        /** @var $observerMock \Magento\Framework\Event\Observer */
        $this->themeObserver->execute($observerMock);
    }
}
