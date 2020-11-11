<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Test\Unit\Model\Export\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Downloadable\Model\LinkRepository;
use Magento\Downloadable\Model\Product\Type as Type;
use Magento\Downloadable\Model\SampleRepository;
use Magento\DownloadableImportExport\Model\Export\RowCustomizer;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Customizes output during export
 */
class RowCustomizerTest extends TestCase
{
    /**
     * @var LinkRepository|MockObject
     */
    private $linkRepository;

    /**
     * @var SampleRepository|MockObject
     */
    private $sampleRepository;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var RowCustomizer
     */
    private $rowCustomizer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->sampleRepository = $this->createMock(SampleRepository::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->rowCustomizer = new RowCustomizer(
            $this->storeManager,
            $this->linkRepository,
            $this->sampleRepository
        );
    }

    /**
     * Test to Prepare downloadable data for export
     */
    public function testPrepareData()
    {
        $productIds = [1, 2, 3];
        $collection = $this->createMock(ProductCollection::class);
        $collection->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with('entity_id', ['in' => $productIds])
            ->willReturnSelf();
        $collection->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with('type_id', ['eq' => Type::TYPE_DOWNLOADABLE])
            ->willReturnSelf();
        $collection->method('addAttributeToSelect')->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->storeManager->expects($this->once())
            ->method('setCurrentStore')
            ->with(Store::DEFAULT_STORE_ID);

        $this->rowCustomizer->prepareData($collection, $productIds);
    }
}
