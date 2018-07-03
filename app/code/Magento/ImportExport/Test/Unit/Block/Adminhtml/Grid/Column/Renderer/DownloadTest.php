<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DownloadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Context
     */
    protected $context;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer\Download
     */
    protected $download;

    /**
     * Set up
     */
    protected function setUp()
    {
        $urlModel = $this->createPartialMock(\Magento\Backend\Model\Url::class, ['getUrl']);
        $urlModel->expects($this->any())->method('getUrl')->willReturn('url');
        $this->context = $this->createPartialMock(\Magento\Backend\Block\Context::class, ['getUrlBuilder']);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($urlModel);
        $data = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->download = $this->objectManagerHelper->getObject(
            \Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer\Download::class,
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
        $row = new \Magento\Framework\DataObject($data);
        $this->assertEquals('<p> file.csv</p><a href="url">Download</a>', $this->download->_getValue($row));
    }
}
