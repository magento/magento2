<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Process\QueueFactory;
use Magento\Deploy\Service\Bundle;
use Magento\Deploy\Service\DeployPackage;
use Magento\Deploy\Service\DeployRequireJsConfig;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Service\DeployTranslationsDictionary;
use Magento\Deploy\Service\MinifyTemplates;
use Magento\Deploy\Strategy\CompactDeploy;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

/**
 * Static Content deploy service class unit tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployStaticContentTest extends TestCase
{
    /**
     * @var DeployStaticContent|Mock
     */
    private $service;

    /**
     * @var DeployStrategyFactory|Mock
     */
    private $deployStrategyFactory;

    /**
     * @var QueueFactory|Mock
     */
    private $queueFactory;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var ObjectManagerInterface|Mock
     */
    private $objectManager;

    /**
     * @var StorageInterface|Mock
     */
    private $versionStorage;

    protected function setUp(): void
    {
        $this->deployStrategyFactory = $this->createPartialMock(DeployStrategyFactory::class, ['create']);
        $this->queueFactory = $this->createPartialMock(QueueFactory::class, ['create']);
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            '',
            false
        );
        $this->objectManager = $this->createPartialMock(ObjectManagerInterface::class, ['create', 'get', 'configure']);
        $this->versionStorage = $this->getMockForAbstractClass(
            StorageInterface::class,
            ['save'],
            '',
            false
        );

        $this->service = new DeployStaticContent(
            $this->objectManager,
            $this->logger,
            $this->versionStorage,
            $this->deployStrategyFactory,
            $this->queueFactory
        );
    }

    /**
     * @param array $options
     * @param string $expectedContentVersion
     * @dataProvider deployDataProvider
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testDeploy($options, $expectedContentVersion)
    {
        $package = $this->createMock(Package::class);
        if ($options['refresh-content-version-only']) {
            $package->expects($this->never())->method('isVirtual');
            $package->expects($this->never())->method('getArea');
            $package->expects($this->never())->method('getTheme');
            $package->expects($this->never())->method('getLocale');
        } else {
            $package->expects($this->exactly(2))->method('isVirtual')->willReturn(false);
            $package->expects($this->exactly(3))->method('getArea')->willReturn('area');
            $package->expects($this->exactly(3))->method('getTheme')->willReturn('theme');
            $package->expects($this->exactly(3))->method('getLocale')->willReturn('locale');
        }
        $packages = ['package' => $package];

        if ($expectedContentVersion) {
            $this->versionStorage->expects($this->once())->method('save')->with($expectedContentVersion);
        } else {
            $this->versionStorage->expects($this->once())->method('save');
        }

        $queue = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        if ($options['refresh-content-version-only']) {
            $this->queueFactory->expects($this->never())->method('create');
        } else {
            $this->queueFactory->expects($this->once())->method('create')->willReturn($queue);
        }

        $strategy = $this->getMockBuilder(CompactDeploy::class)
            ->onlyMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        if ($options['refresh-content-version-only']) {
            $strategy->expects($this->never())->method('deploy');
        } else {
            $strategy->expects($this->once())->method('deploy')
                ->with($options)
                ->willReturn($packages);
            $this->deployStrategyFactory->expects($this->once())
                ->method('create')
                ->with('compact', ['queue' => $queue])
                ->willReturn($strategy);
        }
        $deployPackageService = $this->getMockBuilder(DeployPackage::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $deployRjsConfig = $this->getMockBuilder(DeployRequireJsConfig::class)
            ->onlyMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $deployI18n = $this->getMockBuilder(DeployTranslationsDictionary::class)
            ->onlyMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $deployBundle = $this->getMockBuilder(Bundle::class)
            ->onlyMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $minifyTemplates = $this->getMockBuilder(MinifyTemplates::class)
            ->onlyMethods(['minifyTemplates'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        if ($options['refresh-content-version-only']) {
            $this->objectManager->expects($this->never())->method('create');
            $this->objectManager->expects($this->never())->method('get');
        } else {
            $this->objectManager->expects($this->exactly(4))
                ->method('create')
                ->willReturnCallback(
                    function (
                        $class,
                        $params
                    ) use (
                        $deployPackageService,
                        $deployRjsConfig,
                        $deployI18n,
                        $deployBundle
                    ) {
                        if ($class === DeployPackage::class &&
                            $params === ['logger' => $this->logger]) {
                            return $deployPackageService;
                        } elseif ($class === DeployRequireJsConfig::class &&
                            $params === ['logger' => $this->logger]) {
                            return $deployRjsConfig;
                        } elseif ($class === DeployTranslationsDictionary::class &&
                            $params === ['logger' => $this->logger]) {
                            return $deployI18n;
                        } elseif ($class === Bundle::class &&
                            $params === ['logger' => $this->logger]) {
                            return $deployBundle;
                        }
                    }
                );

            $this->objectManager->expects($this->exactly(1))
                ->method('get')
                ->willReturnCallback(
                    function ($class) use ($minifyTemplates) {
                        if ($class === MinifyTemplates::class) {
                            return $minifyTemplates;
                        }
                    }
                );
        }

        $this->assertNull($this->service->deploy($options));
    }

    /**
     * @return array
     */
    public static function deployDataProvider()
    {
        return [
            [
                [
                    'strategy' =>  'compact',
                    'no-javascript' => false,
                    'no-js-bundle' => false,
                    'no-html-minify' => false,
                    'refresh-content-version-only' => false,
                ],
                null // content version value should not be asserted in this case
            ],
            [
                [
                    'strategy' =>  'compact',
                    'no-javascript' => false,
                    'no-js-bundle' => false,
                    'no-html-minify' => false,
                    'refresh-content-version-only' => false,
                    'content-version' =>  '123456',
                ],
                '123456'
            ],
            [
                [
                    'refresh-content-version-only' => true,
                    'content-version' =>  '654321',
                ],
                '654321'
            ]
        ];
    }

    public function testMaxExecutionTimeOptionPassed()
    {
        $options = [
            DeployStaticOptions::MAX_EXECUTION_TIME           => 100,
            DeployStaticOptions::REFRESH_CONTENT_VERSION_ONLY => false,
            DeployStaticOptions::JOBS_AMOUNT                  => 3,
            DeployStaticOptions::STRATEGY                     => 'compact',
            DeployStaticOptions::NO_JAVASCRIPT                => true,
            DeployStaticOptions::NO_JS_BUNDLE                 => true,
            DeployStaticOptions::NO_HTML_MINIFY               => true,
        ];

        $queueMock = $this->createMock(Queue::class);
        $strategyMock = $this->createMock(CompactDeploy::class);
        $this->queueFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'logger'               => $this->logger,
                    'maxExecTime'          => 100,
                    'maxProcesses'         => 3,
                    'options'              => $options,
                    'deployPackageService' => null
                ]
            )
            ->willReturn($queueMock);
        $this->deployStrategyFactory->expects($this->once())
            ->method('create')
            ->with('compact', ['queue' => $queueMock])
            ->willReturn($strategyMock);

        $this->service->deploy($options);
    }
}
