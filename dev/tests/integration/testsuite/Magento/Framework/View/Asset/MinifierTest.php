<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests for minifier
 */
class MinifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getInstance()->getObjectManager();
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        Bootstrap::getInstance()->reinitialize();
    }

    /**
     * CSS Minifier library test
     *
     * When fails on library update or minification handler replacement:
     * 1 - minify `_files/static/css/styles.css` with new library manually
     * 2 - use DIFF tools to see difference between new minified CSS and old minified one
     * 3 - ensure that all differences are acceptable
     * 4 - ensure that new minified CSS is fully workable in all supported browsers
     * 5 - replace `_files/static/css/styles.magento.min.css` with new minified css
     */
    public function testCssMinifierLibrary()
    {
        /** @var \Magento\Framework\View\Asset\Config $config */
        $config = $this->objectManager->get('Magento\Framework\View\Asset\Config');
        $adapterClass = $config->getAssetMinificationAdapter('css');

        /** @var \Magento\Framework\Code\Minifier\AdapterInterface $adapter */
        $adapter = $this->objectManager->get($adapterClass);
        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.magento.min.css'),
            $adapter->minify(file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.css')),
            'Minified CSS differs from initial minified CSS snapshot. '
            . 'Ensure that new CSS is fully valid for all supported browsers '
            . 'and replace old minified snapshot with new one.'
        );
    }

    /**
     * Test CSS minification
     *
     * @param string $requestedUri
     * @param string $requestedFilePath
     * @param string $testFile
     * @param callable $assertionCallback
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _testCssMinification($requestedUri, $requestedFilePath, $testFile, $assertionCallback)
    {
        /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject $appState */
        $appState = $this->getMock('\Magento\Framework\App\State', ['getMode'], [], '', false);
        $appState->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEFAULT));

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->objectManager->get('Magento\Framework\App\Request\Http');
        $request->setRequestUri($requestedUri);
        $request->setParam('resource', $requestedUri);

        $response = $this->getMockForAbstractClass(
            'Magento\Framework\App\Response\FileInterface',
            [],
            '',
            false,
            false,
            true,
            ['setFilePath']
        );
        $response->expects(
            $this->any()
        )->method(
            'setFilePath'
        )->will(
            $this->returnCallback(
                $assertionCallback
            )
        );

        $publisher = $this->objectManager->create(
            'Magento\Framework\App\View\Asset\Publisher',
            [
                'appState' => $appState
            ]
        );

        /** @var \Magento\Framework\App\StaticResource $staticResourceApp */
        $staticResourceApp = $this->objectManager->create(
            'Magento\Framework\App\StaticResource',
            [
                'response' => $response,
                'publisher' => $publisher
            ]
        );
        $initParams = Bootstrap::getInstance()->getAppInitParams();
        $designPath = $initParams[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]['design']['path'];
        $destFile = $designPath . $requestedFilePath;

        if (!is_readable(dirname($destFile))) {
            mkdir(dirname($destFile), 777, true);
        }

        copy($testFile, $destFile);

        $staticResourceApp->launch();

        unlink($destFile);
    }

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 1
     */
    public function testCssMinification()
    {
        $this->_testCssMinification(
            '/frontend/Magento/blank/en_US/css/styles.css',
            '/frontend/Magento/blank/web/css/styles.css',
            dirname(__DIR__) . '/_files/static/css/styles.css',
            function ($path) {
                $this->assertEquals(
                    file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.magento.min.css'),
                    file_get_contents($path),
                    'Minified files are not equal or minification did not work for requested CSS'
                );
            }
        );
    }

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 0
     */
    public function testCssMinificationOff()
    {
        $this->_testCssMinification(
            '/frontend/Magento/blank/en_US/css/styles.css',
            '/frontend/Magento/blank/web/css/styles.css',
            dirname(__DIR__) . '/_files/static/css/styles.css',
            function ($path) {
                $content = file_get_contents($path);
                $this->assertNotEmpty($content);
                $this->assertContains('Magento/backend', $content);
                $this->assertNotEquals(
                    file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.magento.min.css'),
                    $content,
                    'CSS is minified when minification turned off'
                );
            }
        );
    }

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 1
     */
    public function testCssMinificationForMinifiedFiles()
    {
        $this->_testCssMinification(
            '/frontend/Magento/blank/en_US/css/preminified-styles.min.css',
            '/frontend/Magento/blank/web/css/preminified-styles.min.css',
            dirname(__DIR__) . '/_files/static/css/preminified-styles.min.css',
            function ($path) {
                $content = file_get_contents($path);
                $this->assertNotEmpty($content);
                $this->assertContains('Magento/backend', $content);
                $this->assertContains('semi-minified file', $content);
            }
        );
    }

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 1
     */
    public function testDeploymentWithMinifierEnabled()
    {
        $initDirectories = Bootstrap::getInstance()
            ->getAppInitParams()[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];

        $designPath = $initDirectories['design']['path'];

        $staticPath = $initDirectories['static']['path'];

        $fileToBePublished = $staticPath . '/frontend/Magento/blank/en_US/css/styles.css';
        $destFile = $designPath . '/frontend/Magento/blank/web/css/styles.css';
        $fileToTestPublishing = dirname(__DIR__) . '/_files/static/css/styles.css';

        if (!is_readable(dirname($destFile))) {
            mkdir(dirname($destFile), 777, true);
        }

        copy($fileToTestPublishing, $destFile);

        $omFactory = $this->getMock('\Magento\Framework\App\ObjectManagerFactory', ['create'], [], '', false);
        $omFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->objectManager));

        $output = $this->objectManager->create(
            'Symfony\Component\Console\Output\ConsoleOutput'
        );

        $filesUtil = $this->getMock('\Magento\Framework\App\Utility\Files', [], [], '', false);
        $filesUtil->expects($this->any())
            ->method('getStaticLibraryFiles')
            ->will($this->returnValue([]));

        $filesUtil->expects($this->any())
            ->method('getPhtmlFiles')
            ->will($this->returnValue([]));

        $filesUtil->expects($this->any())
            ->method('getStaticPreProcessingFiles')
            ->will($this->returnValue(
                [
                    ['frontend', 'Magento/blank', '', '', 'css/styles.css', $destFile]
                ]
            ));

        /** @var \Magento\Setup\ModelDeployer $deployer */
        $deployer = $this->objectManager->create(
            'Magento\Setup\Model\Deployer',
            ['filesUtil' => $filesUtil, 'output' => $output, 'isDryRun' => false]
        );

        $deployer->deploy($omFactory, ['en_US']);

        $this->assertFileExists($fileToBePublished);
        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/_files/static/css/styles.magento.min.css'),
            file_get_contents($fileToBePublished),
            'Minified file is not equal or minification did not work for deployed CSS'
        );

        unlink($destFile);
        unlink($fileToBePublished);
    }
}
