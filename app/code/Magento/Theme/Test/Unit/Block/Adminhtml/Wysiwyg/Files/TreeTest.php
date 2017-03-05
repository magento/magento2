<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Wysiwyg\Files;

class TreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Theme\Helper\Storage|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperStorage;

    /**
     * @var \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesTree;

    protected function setUp()
    {
        $this->_helperStorage = $this->getMock(\Magento\Theme\Helper\Storage::class, [], [], '', false);
        $this->_urlBuilder = $this->getMock(\Magento\Backend\Model\Url::class, [], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_filesTree = $objectManagerHelper->getObject(
            \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree::class,
            ['urlBuilder' => $this->_urlBuilder, 'storageHelper' => $this->_helperStorage]
        );
    }

    public function testGetTreeLoaderUrl()
    {
        $requestParams = [
            \Magento\Theme\Helper\Storage::PARAM_THEME_ID => 1,
            \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE => \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
            \Magento\Theme\Helper\Storage::PARAM_NODE => 'root',
        ];
        $expectedUrl = 'some_url';

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getRequestParams'
        )->will(
            $this->returnValue($requestParams)
        );

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/*/treeJson',
            $requestParams
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_filesTree->getTreeLoaderUrl());
    }
}
