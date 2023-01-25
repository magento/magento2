<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCatalog\Test\Unit\Plugin\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor as ProcessorSubject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\MediaGalleryCatalog\Plugin\Product\Gallery\RemoveAssetAfterRemoveImage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for \Magento\MediaGalleryCatalog\Plugin\Product\Gallery\Processor
 */
class RemoveAssetAfterRemoveImageTest extends TestCase
{
    private const STUB_FILE_NAME = 'file';

    /**
     * @var DeleteAssetsByPathsInterface|MockObject
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
     * @var RemoveAssetAfterRemoveImage
     */
    private $plugin;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->processorSubjectMock = $this->createMock(ProcessorSubject::class);
        $this->productMock = $this->createMock(Product::class);

        $this->deleteMediaAssetByPathMock = $this->getMockForAbstractClass(DeleteAssetsByPathsInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            RemoveAssetAfterRemoveImage::class,
            [
                'deleteByPaths' => $this->deleteMediaAssetByPathMock,
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
            ->with([self::STUB_FILE_NAME]);
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
            ->with([self::STUB_FILE_NAME])
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
