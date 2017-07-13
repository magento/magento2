<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Page\Config\Generator;

use \Magento\Framework\View\Page\Config\Generator\Body;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for page config generator model
 */
class BodyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Body
     */
    protected $bodyGenerator;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    protected function setUp()
    {
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->bodyGenerator = $objectManagerHelper->getObject(
            \Magento\Framework\View\Page\Config\Generator\Body::class,
            [
                'pageConfig' => $this->pageConfigMock,
            ]
        );
    }

    public function testProcess()
    {
        $generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $structureMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readerContextMock->expects($this->any())
            ->method('getPageConfigStructure')
            ->willReturn($structureMock);

        $bodyClasses = ['class_1', 'class_2'];
        $structureMock->expects($this->once())
            ->method('getBodyClasses')
            ->will($this->returnValue($bodyClasses));
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('addBodyClass')
            ->withConsecutive(['class_1'], ['class_2']);

        $this->assertEquals(
            $this->bodyGenerator,
            $this->bodyGenerator->process($readerContextMock, $generatorContextMock)
        );
    }
}
