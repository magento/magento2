<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\View\Asset\File;
use Magento\RequireJs\Model\FileManager;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

/**
 * Plugin to add asset integrity value after static content deploy.
 */
class GenerateAssetIntegrity
{
    /**
     * Supported content types.
     *
     * @var array
     */
    private const CONTENT_TYPES = ["js"];

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     */
    public function __construct(
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityRepositoryPool = $integrityRepositoryPool;
    }

    /**
     * Generates integrity after publishing of static assets is complete.
     *
     * @param Publisher $subject
     * @param bool $result
     * @param AssetInterface $asset
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPublish(
        Publisher $subject,
        bool $result,
        AssetInterface $asset
    ): bool {
        if (PHP_SAPI == 'cli' && $asset instanceof LocalInterface) {
            if (in_array($asset->getContentType(), self::CONTENT_TYPES)) {
                $this->generateIntegrity($asset);
            }
        }

        return $result;
    }

    /**
     * Generates integrity for RequireJs config.
     *
     * @param FileManager $subject
     * @param File $result
     *
     * @return File
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsConfigAsset(
        FileManager $subject,
        File $result
    ): File {
        if (PHP_SAPI == 'cli') {
            if (in_array($result->getContentType(), self::CONTENT_TYPES)) {
                $this->generateIntegrity($result);
            }
        }

        return $result;
    }

    /**
     * Generates and stores integrity for a given asset.
     *
     * @param LocalInterface $asset
     *
     * @return void
     */
    private function generateIntegrity(LocalInterface $asset): void
    {
        $integrity = $this->integrityFactory->create(
            [
                "data" => [
                    'hash' => $this->hashGenerator->generate(
                        $asset->getContent()
                    ),
                    'url' => $asset->getUrl()
                ]
            ]
        );

        $area = explode(
            "/",
            parse_url($asset->getUrl(), PHP_URL_PATH)
        )[3];

        $this->integrityRepositoryPool->get($area)
            ->save($integrity);
    }
}
