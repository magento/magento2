<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\Wysiwyg\Files;

use Magento\Backend\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree;
use Magento\Theme\Helper\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TreeTest extends TestCase
{
    /**
     * @var Url|MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Storage|MockObject
     */
    protected $_helperStorage;

    /**
     * @var Tree|MockObject
     */
    protected $_filesTree;

    protected function setUp(): void
    {
        $this->_helperStorage = $this->createMock(Storage::class);
        $this->_urlBuilder = $this->createMock(Url::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_filesTree = $objectManagerHelper->getObject(
            Tree::class,
            ['urlBuilder' => $this->_urlBuilder, 'storageHelper' => $this->_helperStorage]
        );
    }

    public function testGetTreeLoaderUrl()
    {
        $requestParams = [
            Storage::PARAM_THEME_ID => 1,
            Storage::PARAM_CONTENT_TYPE => \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
            Storage::PARAM_NODE => 'root',
        ];
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
            'adminhtml/*/treeJson',
            $requestParams
        )->willReturn(
            $expectedUrl
        );

        $this->assertEquals($expectedUrl, $this->_filesTree->getTreeLoaderUrl());
    }
}
