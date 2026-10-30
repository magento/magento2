<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Test\Unit\Model\Export;

use Magento\DownloadableImportExport\Model\Export\RowCustomizer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Downloadable\Model\LinkRepository;
use Magento\Downloadable\Model\SampleRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RowCustomizerTest for export RowCustomizer
 */
class RowCustomizerTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var LinkRepository|MockObject
     */
    private $linkRepositoryMock;

    /**
     * @var SampleRepository|MockObject
     */
    private $sampleRepositoryMock;

    /**
     * @var RowCustomizer
     */
    private $model;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->linkRepositoryMock = $this->createMock(LinkRepository::class);
        $this->sampleRepositoryMock = $this->createMock(SampleRepository::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            RowCustomizer::class,
            [
                'storeManager' => $this->storeManagerMock,
                'linkRepository' => $this->linkRepositoryMock,
                'sampleRepository' => $this->sampleRepositoryMock,
            ]
        );
    }

    /**
     * Test Prepare configurable data for export
     */
    public function testPrepareData()
    {
        $product1 = $this->createMock(ProductInterface::class);
        $product1->method('getId')->willReturn(1);
        $product2 = $this->createMock(ProductInterface::class);
        $product2->method('getId')->willReturn(2);
        $collection = $this->createMock(Collection::class);

        $callCount = 0;
        $collection->expects($this->atLeastOnce())
            ->method('fetchItem')
            ->willReturnCallback(function () use (&$callCount, $product1, $product2) {
                $callCount++;
                if ($callCount === 1) {
                    return $product1;
                } elseif ($callCount === 2) {
                    return $product2;
                }
            });

        $collection->expects($this->exactly(2))
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $collection->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->linkRepositoryMock->expects($this->exactly(2))
            ->method('getLinksByProduct')
            ->willReturn([]);
        $this->sampleRepositoryMock->expects($this->exactly(2))
            ->method('getSamplesByProduct')
            ->willReturn([]);

        $this->model->prepareData($collection, []);
    }
}
