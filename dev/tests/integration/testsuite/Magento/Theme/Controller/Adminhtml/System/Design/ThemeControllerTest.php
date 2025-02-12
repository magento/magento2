<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class ThemeControllerTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var ScopeConfigInterface|mixed
     */
    private $config;

    /**
     * @var string
     */
    private $imageAdapter;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->_objectManager->get(ScopeConfigInterface::class);
        $this->imageAdapter = $this->config->getValue('dev/image/default_adapter');
    }

    public function testUploadJsAction()
    {
        $name = 'simple-js-file.js';
        $this->createUploadFixture($name, 'application/x-javascript', 'js_files_uploader');
        $theme = $this->_objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class)
            ->getCollection()
            ->getFirstItem();

        $this->getRequest()->setPostValue('id', $theme->getId());
        $this->dispatch('backend/admin/system_design_theme/uploadjs');
        $output = $this->getResponse()->getBody();
        $this->assertStringContainsString('"error":false', $output);
        $this->assertStringContainsString($name, $output);
    }

    public function testUploadFaviconAction()
    {
        $names = ['favicon-x-icon.ico', 'favicon-vnd-microsoft.ico'];
        foreach ($names as $name) {
            $this->createUploadFixture($name, 'image/vnd.microsoft.icon', 'head_shortcut_icon');
            $theme = $this->_objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class)
                ->getCollection()
                ->getFirstItem();
            $this->getRequest()->setPostValue('id', $theme->getId());
            $this->dispatch('backend/admin/design_config_fileUploader/save');
            $output = $this->getResponse()->getBody();
            if (!in_array('imagick', get_loaded_extensions()) || $this->imageAdapter == 'GD2') {
                $this->assertStringContainsString(
                    '{"error":"File validation failed. Check Image Processing Settings in the Store Configuration."',
                    $output
                );
            } else {
                $this->assertStringContainsString('"error":"false"', $output);
                $this->assertStringContainsString($name, $output);
            }
        }
    }

    /**
     * Creates a fixture for testing uploaded file
     *
     * @param string $name
     * @params string $mimeType
     * @return void
     * @throws FileSystemException
     */
    private function createUploadFixture($name, $mimeType, $model)
    {
        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get(\Magento\Framework\Filesystem::class);
        $tmpDir = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $subDir = str_replace('\\', '_', __CLASS__);
        $tmpDir->create($subDir);
        $target = $tmpDir->getAbsolutePath("{$subDir}/{$name}");
        copy(__DIR__ . "/_files/{$name}", $target);
        $_FILES = [
            $model => [
                'name' => $name,
                'type' => $mimeType,
                'tmp_name' => $target,
                'error' => 'false',
                'size' => '28',
            ],
        ];
    }
}
