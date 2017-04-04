<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Deploy\Console\ConsoleLogger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\State as AppState;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Strategy\DeployStrategyFactory;

/**
 * Tests for minifier
 *
 * @magentoComponentsDir Magento/Framework/View/_files/static/theme
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MinifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $staticDir;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    private $origMode;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getInstance()->getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
        /** @var \Magento\TestFramework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\TestFramework\App\State::class);
        $this->origMode = $appState->getMode();
        $appState->setMode(AppState::MODE_DEFAULT);
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(\Magento\Framework\Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        /** @var \Magento\TestFramework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\TestFramework\App\State::class);
        $appState->setMode($this->origMode);
        if ($this->staticDir->isExist('frontend/FrameworkViewMinifier')) {
            $this->staticDir->delete('frontend/FrameworkViewMinifier');
        }
        parent::tearDown();
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
    public function testCSSminLibrary()
    {
        /** @var \Magento\Framework\Code\Minifier\AdapterInterface $adapter */
        $adapter = $this->objectManager->get('cssMinificationAdapter');
        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/_files/static/expected/styles.magento.min.css'),
            $adapter->minify(file_get_contents(dirname(__DIR__) . '/_files/static/theme/web/css/styles.css')),
            'Minified CSS differs from initial minified CSS snapshot. '
            . 'Ensure that new CSS is fully valid for all supported browsers '
            . 'and replace old minified snapshot with new one.'
        );
    }

    /**
     * Test JS minification library
     *
     * @return void
     */
    public function testJshrinkLibrary()
    {
        /** @var \Magento\Framework\Code\Minifier\AdapterInterface $adapter */
        $adapter = $this->objectManager->get('jsMinificationAdapter');
        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/_files/static/expected/test.min.js'),
            $adapter->minify(file_get_contents(dirname(__DIR__) . '/_files/static/theme/web/js/test.js')),
            'Minified JS differs from initial minified JS snapshot. '
            . 'Ensure that new JS is fully valid for all supported browsers '
            . 'and replace old minified snapshot with new one.'
        );
    }

    /**
     * Test CSS minification
     *
     * @param string $requestedUri
     * @param callable $assertionCallback
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _testCssMinification($requestedUri, $assertionCallback)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setRequestUri($requestedUri);
        $request->setParam('resource', $requestedUri);

        $response = $this->getMockBuilder(\Magento\Framework\App\Response\FileInterface::class)
            ->setMethods(['setFilePath'])
            ->getMockForAbstractClass();
        $response
            ->expects($this->any())
            ->method('setFilePath')
            ->will($this->returnCallback($assertionCallback));

        /** @var \Magento\Framework\App\StaticResource $staticResourceApp */
        $staticResourceApp = $this->objectManager->create(
            \Magento\Framework\App\StaticResource::class,
            ['response' => $response]
        );
        $staticResourceApp->launch();
    }

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 0
     * @magentoAppIsolation enabled
     */
    public function testCssMinificationOff()
    {
        $this->_testCssMinification(
            '/frontend/FrameworkViewMinifier/default/en_US/css/styles.css',
            function ($path) {
                $content = file_get_contents($path);
                $this->assertNotEmpty($content);
                $this->assertContains('FrameworkViewMinifier/frontend', $content);
                $this->assertNotEquals(
                    file_get_contents(
                        dirname(__DIR__)
                        . '/_files/static/expected/styles.magento.min.css'
                    ),
                    $content,
                    'CSS is minified when minification turned off'
                );
            }
        );
    }

    /**
     * @magentoConfigFixture current_store dev/css/minify_files 1
     */
    public function testCssMinification()
    {
        $this->_testCssMinification(
            '/frontend/FrameworkViewMinifier/default/en_US/css/styles.min.css',
            function ($path) {
                $this->assertEquals(
                    file_get_contents(
                        dirname(__DIR__)
                        . '/_files/static/expected/styles.magento.min.css'
                    ),
                    file_get_contents($path),
                    'Minified files are not equal or minification did not work for requested CSS'
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
            '/frontend/FrameworkViewMinifier/default/en_US/css/preminified-styles.min.css',
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
        $staticPath = $this->staticDir->getAbsolutePath();

        $fileToBePublished = $staticPath . '/frontend/FrameworkViewMinifier/default/en_US/css/styles.min.css';
        $fileToTestPublishing = dirname(__DIR__) . '/_files/static/theme/web/css/styles.css';

        $omFactory = $this->getMock(\Magento\Framework\App\ObjectManagerFactory::class, ['create'], [], '', false);
        $omFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->objectManager));

        $filesUtil = $this->getMock(\Magento\Framework\App\Utility\Files::class, [], [], '', false);
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
                    ['frontend', 'FrameworkViewMinifier/default', '', '', 'css/styles.css', $fileToTestPublishing]
                ]
            ));

        $this->objectManager->addSharedInstance($filesUtil, \Magento\Framework\App\Utility\Files::class);

        $output = $this->objectManager->create(
            \Symfony\Component\Console\Output\ConsoleOutput::class
        );

        $logger = $this->objectManager->create(
            ConsoleLogger::class,
            ['output' => $output]
        );

        $versionStorage = $this->getMock(
            \Magento\Framework\App\View\Deployment\Version\StorageInterface::class,
            ['save', 'load'],
            [],
            '',
            false
        );

        /** @var \Magento\Deploy\Service\DeployStaticContent $deployService */
        $deployService = $this->objectManager->create(
            \Magento\Deploy\Service\DeployStaticContent::class,
            [
                'objectManager' => $this->objectManager,
                'logger' => $logger,
                'versionStorage' => $versionStorage,
            ]
        );

        $deployService->deploy(
            [
                Options::DRY_RUN => false,
                Options::NO_JAVASCRIPT => true,
                Options::NO_CSS => false,
                Options::NO_LESS => false,
                Options::NO_IMAGES => true,
                Options::NO_FONTS => true,
                Options::NO_HTML => true,
                Options::NO_MISC => true,
                Options::NO_HTML_MINIFY => true,
                Options::AREA => ['frontend'],
                Options::EXCLUDE_AREA => ['none'],
                Options::THEME => ['FrameworkViewMinifier/default'],
                Options::EXCLUDE_THEME => ['none'],
                Options::LANGUAGE => ['en_US'],
                Options::EXCLUDE_LANGUAGE => ['none'],
                Options::JOBS_AMOUNT => 0,
                Options::SYMLINK_LOCALE => false,
                Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_QUICK
            ]
        );

        $this->assertFileExists($fileToBePublished);
        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/_files/static/expected/styles.magento.min.css'),
            file_get_contents($fileToBePublished),
            'Minified file is not equal or minification did not work for deployed CSS'
        );
    }
}
