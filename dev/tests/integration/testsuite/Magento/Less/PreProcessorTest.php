<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Less;

class PreProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\App\Filesystem::PARAM_APP_DIRS => array(
                \Magento\App\Filesystem::PUB_LIB_DIR => array('path' => __DIR__ . '/_files/lib'),
                \Magento\App\Filesystem::THEMES_DIR => array('path' => __DIR__ . '/_files/design')
            )
        ));
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get('Magento\App\State')->setAreaCode('frontend');
    }

    /**
     * @magentoDataFixture Magento/Less/_files/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testProcess()
    {
        /** @var $lessPreProcessor \Magento\Css\PreProcessor\Less */
        $lessPreProcessor = $this->objectManager->create('Magento\Css\PreProcessor\Less');
        /** @var $filesystem \Magento\Filesystem */
        $filesystem = $this->objectManager->get('Magento\Filesystem');
        $targetDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::TMP_DIR);
        $designParams = array('area' => 'frontend', 'theme' => 'test_pre_process');
        /** @var \Magento\View\Service $viewService */
        $viewService = $this->objectManager->get('Magento\View\Service');
        $viewService->updateDesignParams($designParams);
        /** @var $file \Magento\View\Publisher\CssFile */
        $cssFile = $this->objectManager->create('Magento\View\Publisher\CssFile', [
            'filePath'         => 'source/source.css',
            'allowDuplication' => true,
            'viewParams'       => $designParams
        ]);
        $cssTargetFile = $lessPreProcessor->process($cssFile, $targetDirectory);
        /** @var $viewFilesystem \Magento\View\FileSystem */
        $viewFilesystem = $this->objectManager->get('Magento\View\FileSystem');
        $this->assertFileEquals(
            $viewFilesystem->getViewFile('source.css', $designParams),
            $cssTargetFile->getSourcePath()
        );
    }

    /**
     * @magentoDataFixture Magento/Less/_files/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCircularDependency()
    {
        $designParams = array('area' => 'frontend', 'theme' => 'test_pre_process');
        /** @var \Magento\View\Service $viewService */
        $viewService = $this->objectManager->get('Magento\View\Service');
        $viewService->updateDesignParams($designParams);
        /** @var $preProcessor \Magento\Less\PreProcessor */
        $preProcessor = $this->objectManager->create('Magento\Less\PreProcessor');
        $fileList = $preProcessor->processLessInstructions('circular_dependency/import1.less', $designParams);
        $files = [];
        /** @var $lessFile \Magento\Less\PreProcessor\File\Less */
        foreach ($fileList as $lessFile) {
            $this->assertFileExists($lessFile->getPublicationPath());
            $files[] = $lessFile;
        }
        $this->assertNotEmpty($files);
        $files[] = array_shift($files);
        $importedFile = reset($files);
        foreach ($fileList as $lessFile) {
            $importedFilePath = preg_quote($importedFile->getPublicationPath());
            $this->assertRegExp("#{$importedFilePath}#", file_get_contents($lessFile->getPublicationPath()));
            $importedFile = next($files);
        }
    }
}
