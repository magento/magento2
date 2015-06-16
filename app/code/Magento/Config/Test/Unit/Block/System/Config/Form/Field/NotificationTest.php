<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $testCacheValue = '1433259723';
        $testDatetime   = (new \DateTime(null, new \DateTimeZone('UTC')))->setTimestamp($testCacheValue);
        $formattedDate  = (\IntlDateFormatter::formatObject($testDatetime));
        $htmlId         = 'test_HTML_id';
        $label          = 'test_label';

        $cacheMock = $this->getMockBuilder('Magento\Framework\App\CacheInterface')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getFrontend', 'remove', 'save', 'clean'])
            ->getMock();
        $cacheMock->expects($this->any())->method('load')->willReturn($testCacheValue);

        $localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $localeDateMock->expects($this->any())->method('date')->willReturn($testDatetime);
        $localeDateMock->expects($this->any())->method('getDateTimeFormat')->willReturn(null);

        $elementMock = $this->getMockBuilder('Magento\Framework\Data\Form\Element\AbstractElement')
            ->disableOriginalConstructor()
            ->setMethods(['getHtmlId', 'getLabel'])
            ->getMock();
        $elementMock->expects($this->any())->method('getHtmlId')->willReturn($htmlId);
        $elementMock->expects($this->any())->method('getLabel')->willReturn($label);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $notification = $objectManager->getObject(
            'Magento\Config\Block\System\Config\Form\Field\Notification',
            [
                'cache'      => $cacheMock,
                'localeDate' => $localeDateMock,
            ]
        );

        $html = $notification->render($elementMock);

        $this->assertEquals(
            '<tr id="row_' . $htmlId . '">' .
            '<td class="label">' .
            '<label for="' . $htmlId . '">' . $label . '</label>' .
            '</td>' .
            '<td class="value">' .
            $formattedDate .
            '</td>' .
            '<td class="scope-label"></td>' .
            '<td class=""></td>' .
            '</tr>',
            $html
        );
    }
}
