<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity\File;
use Magento\Framework\App\View\Asset\Publisher;

/**
 * Plugin to add asset integrity value after static content deploy
 */
class AfterAssetPublishPlugin
{

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @var File
     */
    private File $file;

    /**
     * Constructor
     *
     * @param SubresourceIntegrityRepository $integrityRepository
     * @param File $file
     */
    public function __construct(
        SubresourceIntegrityRepository $integrityRepository,
        File $file
    ) {
        $this->integrityRepository = $integrityRepository;
        $this->file = $file;
    }

    /**
     * Calculate integrity hash after publishing of static assets is complete
     *
     * @param Publisher $subject
     * @param bool $result
     * @param AssetInterface $asset
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws FileSystemException
     */
    public function afterPublish(
        Publisher $subject,
        bool $result,
        AssetInterface $asset
    ):bool {
        if ($asset->getContentType() === SubresourceIntegrity::CONTENT_TYPE) {
            $this->file->generateIntegrity($asset);
        }
        return $result;
    }
}
