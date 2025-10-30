<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Deploy\Service\DeployStaticContent;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Plugin that stores generated integrity hashes for all assets.
 */
class StoreAssetIntegrityHashes
{
    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        SubresourceIntegrityCollector $integrityCollector,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool,
        ?LoggerInterface $logger = null
    ) {
        $this->integrityCollector = $integrityCollector;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
        $this->logger = $logger ??
            ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Stores generated integrity hashes after static content deploy
     *
     * @param DeployStaticContent $subject
     * @param mixed $result
     * @param array $options
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeploy(
        DeployStaticContent $subject,
        mixed $result,
        array $options
    ): void {
        $bunches = [];
        $integrityHashes = $this->integrityCollector->release();

        foreach ($integrityHashes as $integrity) {
            $area = explode("/", $integrity->getPath())[0];
            $bunches[$area][] = $integrity;
        }

        foreach ($bunches as $area => $bunch) {
            try {
                $this->integrityRepositoryPool->get($area)->saveBunch($bunch);
            } catch (\Exception $e) {
                $this->logger->error('SRI Store: Failed saving ' . $area . ': ' . $e->getMessage());
            }
        }
    }
}
