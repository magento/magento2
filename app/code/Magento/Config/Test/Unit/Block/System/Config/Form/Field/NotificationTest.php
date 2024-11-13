<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Field\Notification
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\Notification;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatter;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testRender()
    {
        $objectManager = new ObjectManager($this);

        $testCacheValue = 1433259723;
        $testDatetime = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($testCacheValue);

        /** @var DateTimeFormatterInterface $dateTimeFormatter */
        $dateTimeFormatter = $objectManager->getObject(DateTimeFormatter::class);
        $localeResolver = $objectManager->getObject(Resolver::class);

        $reflection = new \ReflectionClass(DateTimeFormatter::class);
        $reflectionProperty = $reflection->getProperty('localeResolver');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dateTimeFormatter, $localeResolver);

        $formattedDate = $dateTimeFormatter->formatObject($testDatetime);

        $htmlId = 'test_HTML_id';
        $label = 'test_label';

        $localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $localeDateMock->expects($this->any())->method('date')->willReturn($testDatetime);
        $localeDateMock->expects($this->any())->method('getDateTimeFormat')->willReturn(null);

        $elementMock = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLabel'])
            ->onlyMethods(['getHtmlId'])
            ->getMock();
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn($htmlId);
        $elementMock->expects($this->any())->method('getLabel')->willReturn($label);

        $dateTimeFormatter = $this->getMockForAbstractClass(DateTimeFormatterInterface::class);
        $dateTimeFormatter->expects($this->once())
            ->method('formatObject')
            ->with($testDatetime)
            ->willReturn($formattedDate);

        $objectManager->prepareObjectManager();
        /** @var Notification $notification */
        $notification = $objectManager->getObject(
            Notification::class,
            [
                'localeDate' => $localeDateMock,
                'dateTimeFormatter' => $dateTimeFormatter,
            ]
        );

        $html = $notification->render($elementMock);

        $this->assertEquals(
            '<tr id="row_' . $htmlId . '">' .
            '<td class="label">' .
            '<label for="' . $htmlId . '"><span>' . $label . '</span></label>' .
            '</td>' .
            '<td class="value">' .
            $formattedDate .
            '</td>' .
            '<td class=""></td>' .
            '</tr>',
            $html
        );
    }
}
