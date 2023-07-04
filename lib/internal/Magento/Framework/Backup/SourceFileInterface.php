<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Backup;

/**
 * Interface for work with archives
 *
 * @api
 */
interface SourceFileInterface
{

    /**
     * Check if keep files of backup
     *
     * @return bool
     */
    public function keepSourceFile();

    /**
     * Set if keep files of backup
     *
     * @param bool $keepSourceFile
     * @return $this
     */
    public function setKeepSourceFile(bool $keepSourceFile);
}
