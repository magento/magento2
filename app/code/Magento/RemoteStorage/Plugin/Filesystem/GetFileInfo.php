<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin\Filesystem;

use Magento\Framework\Exception\FileSystemException;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo as Subject;
use Magento\RemoteStorage\Model\TmpFileCopier;

/**
 * Copies file from the remote server to the tmp directory if remote storage is enabled
 */
class GetFileInfo
{
    /**
     * @var TmpFileCopier
     */
    private $tmpFileCopier;

    /**
     * @param TmpFileCopier $tmpFileCopier
     */
    public function __construct(
        TmpFileCopier $tmpFileCopier
    ) {
        $this->tmpFileCopier = $tmpFileCopier;
    }

    /**
     * Copies file from the remote server to the tmp directory
     *
     * @param Subject $subject
     * @param string $path
     * @return array
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(Subject $subject, string $path)
    {
        return [$this->tmpFileCopier->copy($path)];
    }
}
