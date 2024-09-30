<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Test\Unit\Model\Export;

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
     * @var \Magento\DownloadableImportExport\Model\Export\RowCustomizer
     */
    private $model;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->linkRepositoryMock = $this->getMockBuilder(LinkRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sampleRepositoryMock = $this->getMockBuilder(SampleRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\DownloadableImportExport\Model\Export\RowCustomizer::class,
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
        $product1 = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product1->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $product2 = $this->getMockBuilder(ProductInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
        $product2->expects($this->any())
            ->method('getId')
            ->willReturn(2);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->atLeastOnce())
            ->method('fetchItem')
            ->willReturn($product1, $product2);

        $collection->expects($this->exactly(2))
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $collection->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->linkRepositoryMock->expects($this->exactly(2))
            ->method('getLinksByProduct')
            ->will($this->returnValue([]));
        $this->sampleRepositoryMock->expects($this->exactly(2))
            ->method('getSamplesByProduct')
            ->will($this->returnValue([]));

        $this->model->prepareData($collection, []);
    }
}
