<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\Area;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

/**
 * Plugin that removes existing integrity hashes for all assets.
 */
class RemoveAllAssetIntegrityHashes
{
    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     */
    public function __construct(
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool
    ) {
        $this->integrityRepositoryPool = $integrityRepositoryPool;
    }

    /**
     * Removes existing integrity hashes before static content deploy
     *
     * @param DeployStaticContent $subject
     * @param array $options
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeploy(
        DeployStaticContent $subject,
        array $options
    ): void {
        if (PHP_SAPI == 'cli' && !$this->isRefreshContentVersionOnly($options)) {
            foreach ([Package::BASE_AREA, Area::AREA_FRONTEND, Area::AREA_ADMINHTML] as $area) {
                $this->integrityRepositoryPool->get($area)
                    ->deleteAll();
            }
        }
    }

    /**
     * Checks if only version refresh is requested.
     *
     * @param array $options
     *
     * @return bool
     */
    private function isRefreshContentVersionOnly(array $options): bool
    {
        return isset($options[DeployStaticOptions::REFRESH_CONTENT_VERSION_ONLY])
            && $options[DeployStaticOptions::REFRESH_CONTENT_VERSION_ONLY];
    }
}
