<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\ValidatorException;

/**
 * Validate paths to be used with directories.
 */
interface PathValidatorInterface
{
    /**
     * Validate if path can be used with a directory.
     *
     * @param string $directoryPath
     * @param string $path
     * @param string|null $scheme
     * @param bool $absolutePath
     * @throws ValidatorException
     *
     * @return void
     */
    public function validate(
        $directoryPath,
        $path,
        $scheme = null,
        $absolutePath = false
    );
}
