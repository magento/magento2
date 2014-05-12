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
namespace Magento\Framework\Less;

use Magento\Framework\App\State;

class PreProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            array(
                \Magento\Framework\App\Filesystem::PARAM_APP_DIRS => array(
                    \Magento\Framework\App\Filesystem::PUB_LIB_DIR => array('path' => __DIR__ . '/_files/lib'),
                    \Magento\Framework\App\Filesystem::THEMES_DIR => array('path' => __DIR__ . '/_files/design')
                )
            )
        );
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->state = $this->objectManager->get('Magento\Framework\App\State');
    }

    /**
     * @magentoDataFixture Magento/Framework/Less/_files/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testProcess()
    {
        /** @var $lessPreProcessor \Magento\Framework\Css\PreProcessor\Less */
        $lessPreProcessor = $this->objectManager->create('Magento\Framework\Css\PreProcessor\Less');
        /** @var $filesystem \Magento\Framework\Filesystem */
        $filesystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $targetDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::TMP_DIR);
        $designParams = array('area' => 'frontend', 'theme' => 'test_pre_process');
        /** @var \Magento\Framework\View\Service $viewService */
        $viewService = $this->objectManager->get('Magento\Framework\View\Service');
        $viewService->updateDesignParams($designParams);
        /** @var $file \Magento\Framework\View\Publisher\CssFile */
        $cssFile = $this->objectManager->create(
            'Magento\Framework\View\Publisher\CssFile',
            array('filePath' => 'source/source.css', 'allowDuplication' => true, 'viewParams' => $designParams)
        );
        $cssTargetFile = $lessPreProcessor->process($cssFile, $targetDirectory);
        /** @var $viewFilesystem \Magento\Framework\View\FileSystem */
        $viewFilesystem = $this->objectManager->get('Magento\Framework\View\FileSystem');

        $expectedCssFileName = ($this->state->getMode() === State::MODE_DEVELOPER) ? 'source_dev.css' : 'source.css';
        $this->assertFileEquals(
            $viewFilesystem->getViewFile($expectedCssFileName, $designParams),
            $cssTargetFile->getSourcePath()
        );
    }

    /**
     * @magentoDataFixture Magento/Framework/Less/_files/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCircularDependency()
    {
        $designParams = array('area' => 'frontend', 'theme' => 'test_pre_process');
        /** @var \Magento\Framework\View\Service $viewService */
        $viewService = $this->objectManager->get('Magento\Framework\View\Service');
        $viewService->updateDesignParams($designParams);
        /** @var $preProcessor \Magento\Framework\Less\PreProcessor */
        $preProcessor = $this->objectManager->create('Magento\Framework\Less\PreProcessor');
        $fileList = $preProcessor->processLessInstructions('circular_dependency/import1.less', $designParams);
        $files = array();
        /** @var $lessFile \Magento\Framework\Less\PreProcessor\File\Less */
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
