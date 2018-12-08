<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\CollectionTimeLabel;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
<<<<<<< HEAD
=======
use Magento\Framework\Locale\ResolverInterface;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CollectionTimeLabelTest extends \PHPUnit\Framework\TestCase
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

<<<<<<< HEAD
=======
    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    protected function setUp()
    {
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(['getComment'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getLocaleDate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timeZoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getLocaleDate')
            ->willReturn($this->timeZoneMock);
<<<<<<< HEAD
=======
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLocale'])
            ->getMockForAbstractClass();
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

        $objectManager = new ObjectManager($this);
        $this->collectionTimeLabel = $objectManager->getObject(
            CollectionTimeLabel::class,
            [
<<<<<<< HEAD
                'context' => $this->contextMock
=======
                'context' => $this->contextMock,
                'localeResolver' => $this->localeResolver
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            ]
        );
    }

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
<<<<<<< HEAD
=======
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $this->assertRegExp(
            "/Eastern Standard Time \(America\/New_York\)/",
            $this->collectionTimeLabel->render($this->abstractElementMock)
        );
    }
}
