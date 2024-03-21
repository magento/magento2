<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\CatalogGraphQl\Model\Resolver\Category\Image;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use Magento\GraphQl\Model\Query\Context;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @see Image
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    private Image $image;

    /**
     * @var FileInfo
     */
    private FileInfo $fileInfoMock;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryListMock;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    /**
     * @var Category
     */
    private Category $categoryMock;

    /**
     * @var Repository
     */
    private Repository $assetRepoMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        $this->fileInfoMock = $this->createMock(FileInfo::class);
        $this->directoryListMock = $this->createMock(DirectoryList::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->categoryMock = $this->createMock(Category::class);
        $this->assetRepoMock = $this->createMock(Repository::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->image = new Image(
            $this->directoryListMock,
            $this->fileInfoMock,
            $this->assetRepoMock,
            $this->loggerMock
        );
    }

    public function testResolve(): void
    {
        $this->valueMock = ['model' => $this->categoryMock];
        $contextExtensionInterfaceMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStore'])
            ->getMockForAbstractClass();
        $storeMock = $this->createMock(Store::class);
        $this->categoryMock
            ->expects($this->once())
            ->method('getData')
            ->with('image')
            ->willReturn('/media/catalog/category/test.jpg');
        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($contextExtensionInterfaceMock);
        $contextExtensionInterfaceMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('https://magento.url');
        $this->fileInfoMock
            ->expects($this->once())
            ->method('isBeginsWithMediaDirectoryPath')
            ->willReturn('fileName');
        $this->fileInfoMock
            ->expects($this->once())
            ->method('isExist')
            ->willReturn(true);

        $this->assertEquals(
            'https://magento.url/media/catalog/category/test.jpg',
            $this->image->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                $this->valueMock
            )
        );
    }

    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->image->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolveWhenImageFileDoesntExist(): void
    {
        $this->valueMock = ['model' => $this->categoryMock];
        $contextExtensionInterfaceMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStore'])
            ->getMockForAbstractClass();
        $storeMock = $this->createMock(Store::class);
        $this->categoryMock
            ->expects($this->once())
            ->method('getData')
            ->with('image')
            ->willReturn('/media/catalog/category/test.jpg');
        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($contextExtensionInterfaceMock);
        $contextExtensionInterfaceMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('https://magento.url');
        $this->fileInfoMock
            ->expects($this->once())
            ->method('isBeginsWithMediaDirectoryPath')
            ->willReturn('fileName');
        $this->fileInfoMock
            ->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $assetFileMock = $this->createMock(File::class);
        $assetFileMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('https://magento.url/Magento_Catalog/images/category/placeholder/image.jpg');
        $this->assetRepoMock
            ->expects($this->once())
            ->method('createAsset')
            ->with('Magento_Catalog::images/category/placeholder/image.jpg', ['area' => 'frontend'])
            ->willReturn($assetFileMock);
        $this->assertEquals(
            'https://magento.url/Magento_Catalog/images/category/placeholder/image.jpg',
            $this->image->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                $this->valueMock
            )
        );
    }
}
