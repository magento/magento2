<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
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
     * @inheritdoc
     */
    public function validate(
        string $directoryPath,
        string $path,
        $scheme = null,
        $absolutePath = false
    ) {
        $realDirectoryPath = $this->driver->getRealPathSafety($directoryPath);
        if (mb_substr($realDirectoryPath, -1) !== DIRECTORY_SEPARATOR) {
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
            && $path . DIRECTORY_SEPARATOR !== $realDirectoryPath
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
