<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Exception;
use Magento\Deploy\Console\ConsoleLogger;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\FileInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\StaticResource;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\Code\Minifier\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Theme\Model\Theme\Registration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Tests for minifier
 *
 * @magentoComponentsDir Magento/Framework/View/_files/static/theme
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MinifierTest extends TestCase
{
    /**
     * @var WriteInterface
     */
    private $staticDir;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    private $origMode;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getInstance()->getObjectManager();
        /** @var Registration $registration */
        $registration = $this->objectManager->get(Registration::class);
        $registration->register();
        /** @var State $appState */
        $appState = $this->objectManager->get(State::class);
        $this->origMode = $appState->getMode();
        $appState->setMode(AppState::MODE_DEFAULT);
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        /** @var State $appState */
        $appState = $this->objectManager->get(State::class);
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
        /** @var AdapterInterface $adapter */
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
        /** @var AdapterInterface $adapter */
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
     * @throws LocalizedException|Exception
     */
    private function checkCssMinification(string $requestedUri, callable $assertionCallback): void
    {
        /** @var Http $request */
        $request = $this->objectManager->get(Http::class);
        $request->setRequestUri($requestedUri);
        $request->setParam('resource', $requestedUri);

        $response = $this->getMockBuilder(FileInterface::class)
            ->onlyMethods(['setFilePath'])
            ->getMockForAbstractClass();
        $response
            ->expects($this->any())
            ->method('setFilePath')
            ->willReturnCallback($assertionCallback);

        /** @var StaticResource $staticResourceApp */
        $staticResourceApp = $this->objectManager->create(
            StaticResource::class,
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
        $this->checkCssMinification(
            '/frontend/FrameworkViewMinifier/default/en_US/css/styles.css',
            function ($path) {
                $content = file_get_contents($path);
                $this->assertNotEmpty($content);
                $this->assertStringContainsString('FrameworkViewMinifier/frontend', $content);
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
        $this->checkCssMinification(
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
        $this->checkCssMinification(
            '/frontend/FrameworkViewMinifier/default/en_US/css/preminified-styles.min.css',
            function ($path) {
                $content = file_get_contents($path);
                $this->assertNotEmpty($content);
                $this->assertStringContainsString('Magento/backend', $content);
                $this->assertStringContainsString('semi-minified file', $content);
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

        $omFactory = $this->createPartialMock(ObjectManagerFactory::class, ['create']);
        $omFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->objectManager);

        $filesUtil = $this->createMock(Files::class);
        $filesUtil->expects($this->any())
            ->method('getStaticLibraryFiles')
            ->willReturn([]);

        $filesUtil->expects($this->any())
            ->method('getPhtmlFiles')
            ->willReturn([]);

        $filesUtil->expects($this->any())
            ->method('getStaticPreProcessingFiles')
            ->willReturn(
                [
                    ['frontend', 'FrameworkViewMinifier/default', '', '', 'css/styles.css', $fileToTestPublishing]
                ]
            );

        $this->objectManager->addSharedInstance($filesUtil, Files::class);

        $output = $this->objectManager->create(ConsoleOutput::class);

        $logger = $this->objectManager->create(ConsoleLogger::class, ['output' => $output]);

        $versionStorage = $this->createPartialMock(StorageInterface::class, ['save', 'load']);

        /** @var DeployStaticContent $deployService */
        $deployService = $this->objectManager->create(
            DeployStaticContent::class,
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
                Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_QUICK,
                Options::NO_PARENT => false,
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
