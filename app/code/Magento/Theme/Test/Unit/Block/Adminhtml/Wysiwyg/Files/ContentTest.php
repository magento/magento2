<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Wysiwyg\Files;

use Magento\Theme\Model\Wysiwyg\Storage;

class ContentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Url|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Theme\Helper\Storage|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_helperStorage;

    /**
     * @var \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_filesContent;

    /**
     * @var \Magento\Framework\App\RequestInterface|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_request;

    protected function setUp(): void
    {
        $this->_helperStorage = $this->createMock(\Magento\Theme\Helper\Storage::class);
        $this->_urlBuilder = $this->createMock(\Magento\Backend\Model\Url::class);
        $this->_request = $this->createMock(\Magento\Framework\App\RequestInterface::class);

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
        )->willReturn(
            $requestParams
        );

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/*/newFolder',
            $requestParams
        )->willReturn(
            $expectedUrl
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
        )->willReturn(
            $requestParams
        );

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/*/deleteFiles',
            $requestParams
        )->willReturn(
            $expectedUrl
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
        )->willReturn(
            $requestParams
        );

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/*/onInsert',
            $requestParams
        )->willReturn(
            $expectedUrl
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
        )->willReturn(
            $expectedRequest
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
        )->willReturn(
            $expectedUrl
        );

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'type'
        )->willReturn(
            $expectedRequest
        );

        $this->_helperStorage->expects(
            $this->once()
        )->method(
            'getRequestParams'
        )->willReturn(
            $requestParams
        );

        $this->assertEquals($expectedUrl, $this->_filesContent->getContentsUrl());
    }
}
