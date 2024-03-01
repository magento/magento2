<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Deploy\Service\DeployStaticContent;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

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
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     */
    public function __construct(
        SubresourceIntegrityCollector $integrityCollector,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool
    ) {
        $this->integrityCollector = $integrityCollector;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
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

        foreach ($this->integrityCollector->release() as $integrity) {
            $area = explode("/", $integrity->getPath())[0];

            $bunches[$area][] = $integrity;
        }

        foreach ($bunches as $area => $bunch) {
            $this->integrityRepositoryPool->get($area)
                ->saveBunch($bunch);
        }
    }
}
