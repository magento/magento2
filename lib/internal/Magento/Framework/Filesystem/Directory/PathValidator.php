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
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Phrase;

/**
<<<<<<< HEAD
 * {@inheritdoc}
=======
 * @inheritDoc
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 *
 * Validates paths using driver.
 */
class PathValidator implements PathValidatorInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * @inheritDoc
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function validate(
        string $directoryPath,
        string $path,
<<<<<<< HEAD
        $scheme = null,
        $absolutePath = false
    ) {
        $realDirectoryPath = $this->driver->getRealPathSafety($directoryPath);
        if (mb_substr($realDirectoryPath, -1) !== DIRECTORY_SEPARATOR) {
=======
        ?string $scheme = null,
        bool $absolutePath = false
    ): void {
        $realDirectoryPath = $this->driver->getRealPathSafety($directoryPath);
        if ($realDirectoryPath[-1] !== DIRECTORY_SEPARATOR) {
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $realDirectoryPath .= DIRECTORY_SEPARATOR;
        }
        if (!$absolutePath) {
            $actualPath = $this->driver->getRealPathSafety(
                $this->driver->getAbsolutePath(
                    $realDirectoryPath,
                    $path,
                    $scheme
                )
            );
        } else {
            $actualPath = $this->driver->getRealPathSafety($path);
        }

        if (mb_strpos($actualPath, $realDirectoryPath) !== 0
<<<<<<< HEAD
            && $path . DIRECTORY_SEPARATOR !== $realDirectoryPath
=======
            && $path .DIRECTORY_SEPARATOR !== $realDirectoryPath
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        ) {
            throw new ValidatorException(
                new Phrase(
                    'Path "%1" cannot be used with directory "%2"',
                    [$path, $directoryPath]
                )
            );
        }
    }
}
