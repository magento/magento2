<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\FileSystemException;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity\File as FileUtility;
use Magento\RequireJs\Model\FileManager;
use Magento\Framework\View\Asset\File;

/**
 * Plugin to add asset integrity to requirejs-configs
 */
class AfterRequireJsDeployPlugin
{

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @var FileUtility
     */
    private FileUtility $file;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * Constructor
     *
     * @param SubresourceIntegrityRepository $integrityRepository
     * @param FileUtility $file
     * @param Http $request
     */
    public function __construct(
        SubresourceIntegrityRepository $integrityRepository,
        FileUtility $file,
        Http $request
    ) {
        $this->integrityRepository = $integrityRepository;
        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Calculate integrity hash for RequireJs config
     *
     * @param FileManager $subject
     * @param File $result
     * @return File
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsConfigAsset(
        FileManager $subject,
        File $result
    ):File {
        if ($result->getContentType() === SubresourceIntegrity::CONTENT_TYPE) {
            $this->file->generateIntegrity($result);
        }
        return $result;
    }

    /**
     * Calculate integrity hash for RequireJs mixins asset
     *
     * @param FileManager $subject
     * @param File $result
     * @return File
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsMixinsAsset(
        FileManager $subject,
        File $result
    ): File {
        if ($result->getContentType() === SubresourceIntegrity::CONTENT_TYPE) {
            $this->file->generateIntegrity($result);
        }
        return $result;
    }
}
