<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Grpc\App;

use Magento\Framework\App;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Spiral\Goridge;
use Spiral\RoadRunner;

/**
 * Grpc application. Called from grpc worker to serve grpc requests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grpc implements \Magento\Framework\AppInterface
{
    const AREA = 'storefront';

    /**
     * @var State
     */
    private $state;

    /**
     * @var ConfigLoaderInterface
     */
    private $configLoader;

    /**
     * @var \Spiral\GRPC\Server
     */
    private $grpcServer;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var App\ResponseInterface
     */
    private $appResponse;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigLoaderInterface $configLoader
     * @param State $state
     * @param \Spiral\GRPC\Server $grpcServer
     * @param \Psr\Log\LoggerInterface $logger
     * @param App\ResponseInterface $appResponse
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigLoaderInterface $configLoader,
        State $state,
        \Spiral\GRPC\Server $grpcServer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResponseInterface $appResponse
    ) {
        $this->objectManager = $objectManager;
        $this->state = $state;
        $this->configLoader = $configLoader;
        $this->grpcServer = $grpcServer;
        $this->logger = $logger;
        $this->appResponse = $appResponse;
    }

    /**
     * Run application
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     * phpcs:disable Magento2.Security.IncludeFile
     */
    public function launch()
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $this->objectManager->configure($this->configLoader->load(\Magento\Framework\App\Area::AREA_GLOBAL));

        $servicesFile = BP . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR .
            'code' . DIRECTORY_SEPARATOR . 'grpc_services_map.php';
        if (!file_exists($servicesFile)) {
            throw new \RuntimeException(
                $servicesFile . ' is missing in the system.' . PHP_EOL
                . 'Please launch ./bin/magento proto:marshal command.'  . PHP_EOL
                . 'Di compile command may cleanup generated directory. "-s" flag will skip cleanup during di:compile'
            );
        }

        $services = require($servicesFile);
        foreach ($services as $serviceInterface) {
            $serviceInstance = $this->objectManager->get($serviceInterface);
            $this->grpcServer->registerService($serviceInterface, $serviceInstance);
        }
        $relay = $this->objectManager->create(Goridge\StreamRelay::class, [
            'in' => STDIN,
            'out' => STDOUT
        ]);
        $worker = $this->objectManager->create(RoadRunner\Worker::class, ['relay' => $relay]);
        $this->grpcServer->serve($worker);
        return $this->appResponse;
    }

    /**
     * @inheritDoc
     *
     * Ability to handle exceptions that may have occurred during bootstrap and launch.
     *
     * @param App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        $this->logger->error($exception);
        return false;
    }
}
