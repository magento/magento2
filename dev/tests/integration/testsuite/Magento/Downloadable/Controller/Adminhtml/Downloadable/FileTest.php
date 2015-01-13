<?php
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

/**
 * Magento\Downloadable\Controller\Adminhtml\Downloadable\File
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 * @magentoAppArea adminhtml
 */
class FileTest extends \Magento\Backend\Utility\Controller
{
    public function testUploadAction()
    {
        copy(dirname(__DIR__) . '/_files/sample.txt', dirname(__DIR__) . '/_files/sample.tmp');
        $_FILES = [
            'samples' => [
                'name' => 'sample.txt',
                'type' => 'text/plain',
                'tmp_name' => dirname(__DIR__) . '/_files/sample.tmp',
                'error' => 0,
                'size' => 0,
            ],
        ];

        $this->dispatch('backend/admin/downloadable_file/upload/type/samples');
        $body = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Helper\Data'
        )->jsonDecode(
            $body
        );
        $this->assertEquals(0, $result['error']);
    }
}
