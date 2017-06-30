<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Wysiwyg\Files;

use Magento\Theme\Model\Wysiwyg\Storage;

class ContentTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesContent;

    /**
     * @var \Magento\Framework\App\RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    protected function setUp()
    {
        $this->_helperStorage = $this->getMock(\Magento\Theme\Helper\Storage::class, [], [], '', false);
        $this->_urlBuilder = $this->getMock(\Magento\Backend\Model\Url::class, [], [], '', false);
        $this->_request = $this->getMock(\Magento\Framework\App\RequestInterface::class, [], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content::class,
            [
                'urlBuilder' => $this->_urlBuilder,
                'request' => $this->_request,
                'storageHelper' => $this->_helperStorage
            ]
        );
        $this->_filesContent = $objectManagerHelper->getObject(
            \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content::class,
            $constructArguments
        );
    }

    /**
     * @dataProvider requestParamsProvider
     * @param array $requestParams
     */
    public function testGetNewFolderUrl($requestParams)
    {
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
            'adminhtml/*/newFolder',
            $requestParams
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_filesContent->getNewfolderUrl());
    }

    /**
     * @dataProvider requestParamsProvider
     * @param array $requestParams
     */
    public function testGetDeleteFilesUrl($requestParams)
    {
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
            'adminhtml/*/deleteFiles',
            $requestParams
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_filesContent->getDeleteFilesUrl());
    }

    /**
     * @dataProvider requestParamsProvider
     * @param array $requestParams
     */
    public function testGetOnInsertUrl($requestParams)
    {
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
            'adminhtml/*/onInsert',
            $requestParams
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_filesContent->getOnInsertUrl());
    }

    /**
     * Data provider for requestParams
     * @return array
     */
    public function requestParamsProvider()
    {
        return [
            [
                'requestParams' => [
                    \Magento\Theme\Helper\Storage::PARAM_THEME_ID => 1,
                    \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE => Storage::TYPE_IMAGE,
                    \Magento\Theme\Helper\Storage::PARAM_NODE => 'root',
                ]
            ]
        ];
    }

    public function testGetTargetElementId()
    {
        $expectedRequest = 'some_request';

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'target_element_id'
        )->will(
            $this->returnValue($expectedRequest)
        );

        $this->assertEquals($expectedRequest, $this->_filesContent->getTargetElementId());
    }

    public function testGetContentsUrl()
    {
        $expectedUrl = 'some_url';

        $expectedRequest = 'some_request';

        $requestParams = [
            \Magento\Theme\Helper\Storage::PARAM_THEME_ID => 1,
            \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE => Storage::TYPE_IMAGE,
            \Magento\Theme\Helper\Storage::PARAM_NODE => 'root',
        ];

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/*/contents',
            ['type' => $expectedRequest] + $requestParams
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'type'
        )->will(
            $this->returnValue($expectedRequest)
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getRequestParams'
        )->will(
            $this->returnValue($requestParams)
        );

        $this->assertEquals($expectedUrl, $this->_filesContent->getContentsUrl());
    }
}
