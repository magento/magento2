<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin\MediaGalleryMetadata;

use Magento\Framework\Exception\FileSystemException;
use Magento\MediaGalleryMetadata\Model\IptcEmbed as Subject;
use Magento\RemoteStorage\Model\TmpFileCopier;

/**
 * Copies file from the remote server to the tmp directory if remote storage is enabled
 */
class IptcEmbed
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
     * @param string $iptcData
     * @param string $filePath
     * @return array
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGet(Subject $subject, string $iptcData, string $filePath)
    {
        return [$iptcData, $this->tmpFileCopier->copy($filePath)];
    }
}
