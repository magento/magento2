<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Url;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer\Download;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer\Download class.
 */
class DownloadTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Download
     */
    protected $download;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
        $urlModel = $this->createPartialMock(Url::class, ['getUrl']);
        $urlModel->expects($this->any())->method('getUrl')->willReturn('url');
        $this->context = $this->createPartialMock(Context::class, ['getUrlBuilder', 'getEscaper']);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($urlModel);
        $this->context->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);
        $data = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->download = $this->objectManagerHelper->getObject(
            Download::class,
            [
                'context' => $this->context,
                'data' => $data,
            ]
        );
    }

    /**
     * Test _getValue()
     */
    public function testGetValue()
    {
        $data = ['imported_file' => 'file.csv'];
        $row = new DataObject($data);
        $this->escaperMock->expects($this->at(0))
            ->method('escapeHtml')
            ->with('file.csv')
            ->willReturn('file.csv');
        $this->escaperMock->expects($this->at(1))
            ->method('escapeHtml')
            ->with('Download')
            ->willReturn('Download');
        $this->assertEquals('<p> file.csv</p><a href="url">Download</a>', $this->download->_getValue($row));
    }
}
