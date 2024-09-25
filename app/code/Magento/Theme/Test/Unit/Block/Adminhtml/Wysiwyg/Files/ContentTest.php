<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\Wysiwyg\Files;

use Magento\Backend\Model\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Content;
use Magento\Theme\Model\Wysiwyg\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    /**
     * @var Url|MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Theme\Helper\Storage|MockObject
     */
    protected $_helperStorage;

    /**
     * @var Content|MockObject
     */
    protected $_filesContent;

    /**
     * @var RequestInterface|MockObject
     */
    protected $_request;

    protected function setUp(): void
    {
        $this->_helperStorage = $this->createMock(\Magento\Theme\Helper\Storage::class);
        $this->_urlBuilder = $this->createMock(Url::class);
        $this->_request = $this->getMockForAbstractClass(RequestInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            Content::class,
            [
                'urlBuilder' => $this->_urlBuilder,
                'request' => $this->_request,
                'storageHelper' => $this->_helperStorage
            ]
        );
        $this->_filesContent = $objectManagerHelper->getObject(
            Content::class,
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
    public static function requestParamsProvider()
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
