<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Deploy\Process\QueueFactory;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Main service for static content deployment
 *
 * Aggregates services to deploy static files, static files bundles, translations and minified templates
 */
class DeployStaticContent
{
    /**
     * @var DeployStrategyFactory
     */
    private $deployStrategyFactory;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StorageInterface
     */
    private $versionStorage;

    /**
     * DeployStaticContent constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param StorageInterface $versionStorage
     * @param DeployStrategyFactory $deployStrategyFactory
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        StorageInterface $versionStorage,
        DeployStrategyFactory $deployStrategyFactory,
        QueueFactory $queueFactory
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->versionStorage = $versionStorage;
        $this->deployStrategyFactory = $deployStrategyFactory;
        $this->queueFactory = $queueFactory;
    }

    /**
     * Run deploy procedure
     *
     * @param array $options
     * @return void
     */
    public function deploy(array $options)
    {
        $queue = $this->queueFactory->create(
            [
                'logger' => $this->logger,
                'options' => $options,
                'maxProcesses' => $this->getProcessesAmount($options),
                'deployPackageService' => $this->objectManager->create(
                    \Magento\Deploy\Service\DeployPackage::class,
                    [
                        'logger' => $this->logger
                    ]
                )
            ]
        );

        $deployStrategy = $this->deployStrategyFactory->create(
            $options[Options::STRATEGY],
            [
                'queue' => $queue
            ]
        );

        $version = !empty($options[Options::CONTENT_VERSION]) && is_string($options[Options::CONTENT_VERSION])
            ? $options[Options::CONTENT_VERSION]
            : (new \DateTime())->getTimestamp();
        $this->versionStorage->save($version);

        $packages = $deployStrategy->deploy($options);

        if ($options[Options::NO_JAVASCRIPT] !== true) {
            $deployRjsConfig = $this->objectManager->create(DeployRequireJsConfig::class, [
                'logger' => $this->logger
            ]);
            $deployI18n = $this->objectManager->create(DeployTranslationsDictionary::class, [
                'logger' => $this->logger
            ]);
            $deployBundle = $this->objectManager->create(Bundle::class, [
                'logger' => $this->logger
            ]);
            foreach ($packages as $package) {
                if (!$package->isVirtual()) {
                    $deployRjsConfig->deploy($package->getArea(), $package->getTheme(), $package->getLocale());
                    $deployI18n->deploy($package->getArea(), $package->getTheme(), $package->getLocale());
                    $deployBundle->deploy($package->getArea(), $package->getTheme(), $package->getLocale());
                }
            }
        }

        if ($options[Options::NO_HTML_MINIFY] !== true) {
            $this->objectManager->get(MinifyTemplates::class)->minifyTemplates();
        }
    }

    /**
     * @param array $options
     * @return int
     */
    private function getProcessesAmount(array $options)
    {
        return isset($options[Options::JOBS_AMOUNT]) ? (int)$options[Options::JOBS_AMOUNT] : 0;
    }
}
