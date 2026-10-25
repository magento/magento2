<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Deploy\Service\DeployStaticContent;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

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
            $path = $integrity->getPath();

            if (empty($path)) {
                $this->logger->debug('SRI: Skipping empty path');
                continue;
            }

            $pathParts = explode("/", $path);

            // Ensure we have at least 4 segments: area/vendor/theme/locale
            if (count($pathParts) < 4) {
                $this->logger->debug('SRI: Skipping invalid path (< 4 segments)', ['path' => $path]);
                continue;
            }

            $context = implode('/', array_slice($pathParts, 0, 4));
            $bunches[$context][] = $integrity;
        }

        foreach ($bunches as $context => $bunch) {
            try {
                $this->integrityRepositoryPool->get($context)->saveBunch($bunch);
            } catch (\Exception $e) {
                $this->logger->error('SRI Store: Failed saving ' . $context . ': ' . $e->getMessage());
            }
        }
    }
}
