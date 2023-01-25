<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Page\Config\Generator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Generator\Context;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Generator\Body;
use Magento\Framework\View\Page\Config\Structure;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * Test for page config generator model
 */
class BodyTest extends TestCase
{
    /**
     * @var Body
     */
    protected $bodyGenerator;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    protected function setUp(): void
    {
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->bodyGenerator = $objectManagerHelper->getObject(
            Body::class,
            [
                'pageConfig' => $this->pageConfigMock,
            ]
        );
    }

    public function testProcess()
    {
        $generatorContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readerContextMock->expects($this->any())
            ->method('getPageConfigStructure')
            ->willReturn($structureMock);

        $bodyClasses = ['class_1', 'class--2'];
        $structureMock->expects($this->once())
            ->method('getBodyClasses')
            ->willReturn($bodyClasses);
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('addBodyClass')
            ->withConsecutive(['class_1'], ['class--2']);

        $this->assertEquals(
            $this->bodyGenerator,
            $this->bodyGenerator->process($readerContextMock, $generatorContextMock)
        );
    }
}
