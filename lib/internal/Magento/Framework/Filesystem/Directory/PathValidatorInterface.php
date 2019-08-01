<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
     * @param bool $absolutePath Is given path an absolute path?.
     * @throws ValidatorException
     *
     * @return void
     */
    public function validate(
        string $directoryPath,
        string $path,
        ?string $scheme = null,
        bool $absolutePath = false
    ): void;
}
