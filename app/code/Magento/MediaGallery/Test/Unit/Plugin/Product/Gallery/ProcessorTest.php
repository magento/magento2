<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Plugin\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor as ProcessorSubject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\MediaGallery\Plugin\Product\Gallery\Processor;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for \Magento\MediaGallery\Plugin\Product\Gallery\Processor
 */
class ProcessorTest extends TestCase
{
    private const STUB_FILE_NAME = 'file';

    /**
     * @var DeleteByPathInterface|MockObject
     */
    private $deleteMediaAssetByPathMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ProcessorSubject|MockObject
     */
    private $processorSubjectMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Processor
     */
    private $plugin;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->processorSubjectMock = $this->createMock(ProcessorSubject::class);
        $this->productMock = $this->createMock(Product::class);

        $this->deleteMediaAssetByPathMock = $this->getMockBuilder(DeleteByPathInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical'])
            ->getMockForAbstractClass();

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            Processor::class,
            [
                'deleteMediaAssetByPath' => $this->deleteMediaAssetByPathMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Successful test case.
     */
    public function testAfterRemoveImageExpectsExecuteCalled()
    {
        $this->deleteMediaAssetByPathMock->expects($this->once())
            ->method('execute')
            ->with(self::STUB_FILE_NAME);
        $this->loggerMock->expects($this->never())->method('critical');

        $actualResult = $this->plugin->afterRemoveImage(
            $this->processorSubjectMock,
            $this->processorSubjectMock,
            $this->productMock,
            self::STUB_FILE_NAME
        );
        $this->assertSame($this->processorSubjectMock, $actualResult);
    }

    /**
     * Test case when passed File argument is not a string.
     */
    public function testAfterRemoveImageWithIncorrectFile()
    {
        $this->deleteMediaAssetByPathMock->expects($this->never())->method('execute');
        $this->loggerMock->expects($this->never())->method('critical');

        $actualResult = $this->plugin->afterRemoveImage(
            $this->processorSubjectMock,
            $this->processorSubjectMock,
            $this->productMock,
            ['non-string-argument' => self::STUB_FILE_NAME]
        );
        $this->assertSame($this->processorSubjectMock, $actualResult);
    }

    /**
     * Test case when an Exception is thrown.
     */
    public function testAfterRemoveImageExpectsExecuteWillThrowException()
    {
        $this->deleteMediaAssetByPathMock->expects($this->once())
            ->method('execute')
            ->with(self::STUB_FILE_NAME)
            ->willThrowException(new \Exception('Some Exception'));
        $this->loggerMock->expects($this->once())->method('critical');

        $actualResult = $this->plugin->afterRemoveImage(
            $this->processorSubjectMock,
            $this->processorSubjectMock,
            $this->productMock,
            self::STUB_FILE_NAME
        );
        $this->assertSame($this->processorSubjectMock, $actualResult);
    }
}
