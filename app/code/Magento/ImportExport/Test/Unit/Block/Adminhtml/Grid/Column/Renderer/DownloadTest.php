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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer\Download;
use PHPUnit\Framework\TestCase;

class DownloadTest extends TestCase
{
    /**
     * @var Context
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
     * Set up
     */
    protected function setUp(): void
    {
        $urlModel = $this->createPartialMock(Url::class, ['getUrl']);
        $urlModel->expects($this->any())->method('getUrl')->willReturn('url');
        $this->context = $this->createPartialMock(Context::class, ['getUrlBuilder']);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($urlModel);
        $data = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->download = $this->objectManagerHelper->getObject(
            Download::class,
            [
                'context' => $this->context,
                'data' => $data
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
        $this->assertEquals('<p> file.csv</p><a href="url">Download</a>', $this->download->_getValue($row));
    }
}
