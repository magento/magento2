<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Field\Notification
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

class NotificationTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $testCacheValue = '1433259723';
        $testDatetime = (new \DateTime(null, new \DateTimeZone('UTC')))->setTimestamp($testCacheValue);

        /** @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter */
        $dateTimeFormatter = $objectManager->getObject(\Magento\Framework\Stdlib\DateTime\DateTimeFormatter::class);
        $localeResolver = $objectManager->getObject(\Magento\Framework\Locale\Resolver::class);

        $reflection = new \ReflectionClass(\Magento\Framework\Stdlib\DateTime\DateTimeFormatter::class);
        $reflectionProperty = $reflection->getProperty('localeResolver');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dateTimeFormatter, $localeResolver);

        $formattedDate = $dateTimeFormatter->formatObject($testDatetime);

        $htmlId = 'test_HTML_id';
        $label = 'test_label';

        $localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeDateMock->expects($this->any())->method('date')->willReturn($testDatetime);
        $localeDateMock->expects($this->any())->method('getDateTimeFormat')->willReturn(null);

        $elementMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHtmlId', 'getLabel'])
            ->getMock();
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn($htmlId);
        $elementMock->expects($this->any())->method('getLabel')->willReturn($label);

        $dateTimeFormatter = $this->getMock(\Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface::class);
        $dateTimeFormatter->expects($this->once())
            ->method('formatObject')
            ->with($testDatetime)
            ->willReturn($formattedDate);

        /** @var \Magento\Config\Block\System\Config\Form\Field\Notification $notification */
        $notification = $objectManager->getObject(
            \Magento\Config\Block\System\Config\Form\Field\Notification::class,
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
