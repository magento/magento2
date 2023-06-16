<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\Catalog\Model\Product\Option\Type\File\ExistingValidate as Subject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\RemoteStorage\Model\TmpFileCopier;

/**
 * @see AbstractAdapter
 */
class ExistingValidate
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
     * @param string $value
     * @param string|null $originalName
     * @return array
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeIsValid(Subject $subject, $value, string $originalName = null)
    {
        return [$this->tmpFileCopier->copy($value), $originalName];
    }
}
