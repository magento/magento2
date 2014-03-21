<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Model\Product\CopyConstructor;

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
    protected $_encoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productTypeMock;

    protected function setUp()
    {
        $this->_encoderMock = $this->getMock('\Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_model = new \Magento\Downloadable\Model\Product\CopyConstructor\Downloadable($this->_encoderMock);

        $this->_productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->_duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('setDownloadableData', '__wakeup'),
            array(),
            '',
            false
        );

        $this->_linkMock = $this->getMock('\Magento\Downloadable\Model\Link', array(), array(), '', false);

        $this->_sampleMock = $this->getMock('\Magento\Downloadable\Model\Sample', array(), array(), '', false);

        $this->_productTypeMock = $this->getMock(
            '\Magento\Downloadable\Model\Product\Type',
            array(),
            array(),
            '',
            false
        );

        $this->_encoderMock->expects($this->any())->method('jsonEncode')->will($this->returnArgument(0));
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
            $this->returnValue(array($this->_linkMock))
        );

        $this->_productTypeMock->expects(
            $this->once()
        )->method(
            'getSamples'
        )->with(
            $this->_productMock
        )->will(
            $this->returnValue(array($this->_sampleMock))
        );

        $linkData = array(
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
            'number_of_downloads' => 'number_of_downloads'
        );

        $sampleData = array(
            'title' => 'title',
            'sample_type' => 'sample_type',
            'sample_file' => 'sample_file',
            'sample_url' => 'sample_url',
            'sort_order' => 'sort_order'
        );

        $this->_linkMock->expects($this->once())->method('getData')->will($this->returnValue($linkData));
        $this->_sampleMock->expects($this->once())->method('getData')->will($this->returnValue($sampleData));

        $this->_duplicateMock->expects($this->once())->method('setDownloadableData')->with($expectedData);
        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
