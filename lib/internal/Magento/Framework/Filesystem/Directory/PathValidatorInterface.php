<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
     * @param bool $absolutePath
=======
     * @param bool $absolutePath Is given path an absolute path?.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @throws ValidatorException
     *
     * @return void
     */
    public function validate(
        string $directoryPath,
        string $path,
<<<<<<< HEAD
        $scheme = null,
        $absolutePath = false
    );
=======
        ?string $scheme = null,
        bool $absolutePath = false
    ): void;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
