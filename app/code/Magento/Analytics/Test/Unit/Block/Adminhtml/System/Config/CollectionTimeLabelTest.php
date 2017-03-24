<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Unit\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form;
use Magento\Analytics\Block\Adminhtml\System\Config\CollectionTimeLabel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class CollectionTimeLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionTimeLabel
     */
    private $collectionTimeLabel;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timeZoneMock;

    /**
     * @var AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractElementMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(['getComment'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timeZoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionTimeLabel = new CollectionTimeLabel($this->contextMock, $this->timeZoneMock);
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $timeZone = "America/New_York";
        $this->abstractElementMock->setForm($this->formMock);
        $this->timeZoneMock->expects($this->once())
            ->method('getConfigTimezone')
            ->willReturn($timeZone);
        $this->abstractElementMock->expects($this->any())
            ->method('getComment')
            ->willReturn('Eastern Standard Time (America/New_York)');
        $this->assertRegexp(
            "/Eastern Standard Time \(America\/New_York\)/",
            $this->collectionTimeLabel->render($this->abstractElementMock)
        );
    }
}
