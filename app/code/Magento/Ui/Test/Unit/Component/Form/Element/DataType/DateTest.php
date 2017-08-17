<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Element\DataType\Date;

class DateTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $localeDateMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $localeResolverMock;

    /** @var \Magento\Ui\Component\Form\Element\DataType\Date  */
    private $date;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $processorMock;

    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    public function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        $this->objectManagerHelper = new ObjectManager($this);
        $this->processorMock = $this->createMock(Processor::class);
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processorMock);
    }

    public function testPrepareWithTimeOffset()
    {
        $this->date = new Date(
            $this->contextMock,
            $this->localeDateMock,
            $this->localeResolverMock,
            [],
            [
                'config' => [
                    'timeOffset' => 1,
                ],
            ]
        );

        $localeDateFormat = 'dd/MM/y';

        $this->localeDateMock->expects($this->once())
            ->method('getDateFormat')
            ->willReturn($localeDateFormat);

        $this->date->prepare();

        $config = $this->date->getConfig();
        $this->assertTrue(is_array($config));

        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('dateFormat', $config['options']);
        $this->assertEquals($localeDateFormat, $config['options']['dateFormat']);
    }

    public function testPrepareWithoutTimeOffset()
    {
        $defaultDateFormat = 'MM/dd/y';

        $this->date = new Date(
            $this->contextMock,
            $this->localeDateMock,
            $this->localeResolverMock,
            [],
            [
                'config' => [
                    'options' => [
                        'dateFormat' => $defaultDateFormat,
                    ],
                    'outputDateFormat' => $defaultDateFormat,
                ],
            ]
        );

        $localeDateFormat = 'dd/MM/y';

        $this->localeDateMock->expects($this->once())
            ->method('getDateFormat')
            ->willReturn($localeDateFormat);
        $this->localeDateMock->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('America/Los_Angeles');

        $this->date->prepare();

        $config = $this->date->getConfig();
        $this->assertTrue(is_array($config));

        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('dateFormat', $config['options']);
        $this->assertEquals($localeDateFormat, $config['options']['dateFormat']);
    }

    /**
     * This tests ensures that userTimeZone is properly saved in the configuration
     */
    public function testPrepare()
    {
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('de-DE');
        $this->date = $this->objectManagerHelper->getObject(
            Date::class,
            [
                'context' => $this->contextMock,
                'localeDate' => $this->localeDateMock,
                'localeResolver' => $this->localeResolverMock
            ]
        );
        $this->localeDateMock->expects($this->any())->method('getConfigTimezone')->willReturn('America/Chicago');
        $this->date->prepare();
        $configArray = $this->date->getData('config');
        $this->assertEquals('America/Chicago', $configArray['storeTimeZone']);
        $this->assertEquals('de-DE', $configArray['options']['storeLocale']);
    }
}
