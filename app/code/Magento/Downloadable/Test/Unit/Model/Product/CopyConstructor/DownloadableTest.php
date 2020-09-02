<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Product\CopyConstructor\Downloadable;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Sample;
use Magento\Framework\Json\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DownloadableTest extends TestCase
{
    /**
     * @var Downloadable
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_productMock;

    /**
     * @var MockObject
     */
    protected $_duplicateMock;

    /**
     * @var MockObject
     */
    protected $_linkMock;

    /**
     * @var MockObject
     */
    protected $_sampleMock;

    /**
     * @var MockObject
     */
    protected $_linkCollectionMock;

    /**
     * @var MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var MockObject
     */
    protected $_productTypeMock;

    protected function setUp(): void
    {
        $this->jsonHelperMock = $this->createMock(Data::class);
        $this->_model = new Downloadable($this->jsonHelperMock);

        $this->_productMock = $this->createMock(Product::class);

        $this->_duplicateMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setDownloadableData'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_linkMock = $this->createMock(Link::class);

        $this->_sampleMock = $this->createMock(Sample::class);

        $this->_productTypeMock = $this->createMock(Type::class);

        $this->jsonHelperMock->expects($this->any())->method('jsonEncode')->willReturnArgument(0);
    }

    public function testBuildWithNonDownloadableProductType()
    {
        $this->_productMock->expects($this->once())->method('getTypeId')->willReturn('some value');

        $this->_duplicateMock->expects($this->never())->method('setDownloadableData');

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }

    public function testBuild()
    {
        $expectedData = include __DIR__ . '/_files/expected_data.php';

        $this->_productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            Type::TYPE_DOWNLOADABLE
        );

        $this->_productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->_productTypeMock
        );

        $this->_productTypeMock->expects(
            $this->once()
        )->method(
            'getLinks'
        )->with(
            $this->_productMock
        )->willReturn(
            [$this->_linkMock]
        );

        $this->_productTypeMock->expects(
            $this->once()
        )->method(
            'getSamples'
        )->with(
            $this->_productMock
        )->willReturn(
            [$this->_sampleMock]
        );

        $linkData = [
            'title' => 'title',
            'is_shareable' => 'is_shareable',
            'sample_type' => 'sample_type',
            'sample_url' => 'sample_url',
            'sample_file' => 'sample_file',
            'link_file' => 'link_file',
            'link_type' => 'link_type',
            'link_url' => 'link_url',
            'sort_order' => 'sort_order',
            'price' => 'price',
            'number_of_downloads' => 'number_of_downloads',
        ];

        $sampleData = [
            'title' => 'title',
            'sample_type' => 'sample_type',
            'sample_file' => 'sample_file',
            'sample_url' => 'sample_url',
            'sort_order' => 'sort_order',
        ];

        $this->_linkMock->expects($this->once())->method('getData')->willReturn($linkData);
        $this->_sampleMock->expects($this->once())->method('getData')->willReturn($sampleData);

        $this->_duplicateMock->expects($this->once())->method('setDownloadableData')->with($expectedData);
        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
