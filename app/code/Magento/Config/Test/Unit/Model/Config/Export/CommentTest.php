<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\Model\Config\Export\Comment;
use Magento\Config\App\Config\Source\DumpConfigSourceInterface;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DumpConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configSourceMock;

    /**
     * @var PlaceholderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeholderMock;

    /**
     * @var Comment
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->placeholderMock = $this->getMockBuilder(PlaceholderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $placeholderFactoryMock = $this->getMockBuilder(PlaceholderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $placeholderFactoryMock->expects($this->once())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->configSourceMock = $this->getMockBuilder(DumpConfigSourceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            Comment::class,
            [
                'placeholderFactory' => $placeholderFactoryMock,
                'source' => $this->configSourceMock
            ]
        );
    }

    public function testGetEmpty()
    {
        $this->configSourceMock->expects($this->once())
            ->method('getExcludedFields')
            ->willReturn([]);
        $this->assertEmpty($this->model->get());
    }

    public function testGet()
    {
        $path = 'one/two';
        $placeholder = 'one__two';
        $expectedResult = 'The configuration file doesn\'t contain sensitive data for security reasons. '
            . 'Sensitive data can be stored in the following environment variables:'
            . "\n$placeholder for $path";

        $this->configSourceMock->expects($this->once())
            ->method('getExcludedFields')
            ->willReturn([$path]);

        $this->placeholderMock->expects($this->once())
            ->method('generate')
            ->with($path)
            ->willReturn($placeholder);

        $this->assertEquals($expectedResult, $this->model->get());
    }
}
