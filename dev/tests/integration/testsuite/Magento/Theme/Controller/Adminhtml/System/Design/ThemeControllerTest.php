<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class ThemeControllerTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testUploadJsAction()
    {
        $name = 'simple-js-file.js';
        $this->createUploadFixture($name);
        $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface')
            ->getCollection()
            ->getFirstItem();

        $this->getRequest()->setPostValue('id', $theme->getId());
        $this->dispatch('backend/admin/system_design_theme/uploadjs');
        $output = $this->getResponse()->getBody();
        $this->assertContains('"error":false', $output);
        $this->assertContains($name, $output);
    }

    /**
     * Creates a fixture for testing uploaded file
     *
     * @param string $name
     * @return void
     */
    private function createUploadFixture($name)
    {
        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $tmpDir = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $subDir = str_replace('\\', '_', __CLASS__);
        $tmpDir->create($subDir);
        $target = $tmpDir->getAbsolutePath("{$subDir}/{$name}");
        copy(__DIR__ . "/_files/{$name}", $target);
        $_FILES = [
            'js_files_uploader' => [
                'name' => 'simple-js-file.js',
                'type' => 'application/x-javascript',
                'tmp_name' => $target,
                'error' => '0',
                'size' => '28',
            ],
        ];
    }
}
