<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Product\CopyConstructor;

class DownloadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\CopyConstructor\Downloadable
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_duplicateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_linkMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sampleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_linkCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productTypeMock;

    protected function setUp()
    {
        $this->jsonHelperMock = $this->getMock('\Magento\Framework\Json\Helper\Data', [], [], '', false);
        $this->_model = new \Magento\Downloadable\Model\Product\CopyConstructor\Downloadable($this->jsonHelperMock);

        $this->_productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $this->_duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['setDownloadableData', '__wakeup'],
            [],
            '',
            false
        );

        $this->_linkMock = $this->getMock('\Magento\Downloadable\Model\Link', [], [], '', false);

        $this->_sampleMock = $this->getMock('\Magento\Downloadable\Model\Sample', [], [], '', false);

        $this->_productTypeMock = $this->getMock(
            '\Magento\Downloadable\Model\Product\Type',
            [],
            [],
            '',
            false
        );

        $this->jsonHelperMock->expects($this->any())->method('jsonEncode')->will($this->returnArgument(0));
    }

    public function testBuildWithNonDownloadableProductType()
    {
        $this->_productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('some value'));

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
        )->will(
            $this->returnValue(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
        );

        $this->_productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->_productTypeMock)
        );

        $this->_productTypeMock->expects(
            $this->once()
        )->method(
            'getLinks'
        )->with(
            $this->_productMock
        )->will(
            $this->returnValue([$this->_linkMock])
        );

        $this->_productTypeMock->expects(
            $this->once()
        )->method(
            'getSamples'
        )->with(
            $this->_productMock
        )->will(
            $this->returnValue([$this->_sampleMock])
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

        $this->_linkMock->expects($this->once())->method('getData')->will($this->returnValue($linkData));
        $this->_sampleMock->expects($this->once())->method('getData')->will($this->returnValue($sampleData));

        $this->_duplicateMock->expects($this->once())->method('setDownloadableData')->with($expectedData);
        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
