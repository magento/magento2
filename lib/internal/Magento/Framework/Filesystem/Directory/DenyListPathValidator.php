<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Phrase;

/**
 * Validates paths using driver.
 */
class DenyListPathValidator implements PathValidatorInterface
{
    /**
     * File deny list using regular expressions
     *
     * @var string[]
     */
    private $fileDenyList = ["htaccess"];

    /**
     * Deny list exception list
     *
     * @var string[]
     */
    private $exceptionList = [];

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
     * @inheritDoc
     */
    public function validate(
        string $directoryPath,
        string $path,
        ?string $scheme = null,
        bool $absolutePath = false
    ): void {
        $realDirectoryPath = $this->driver->getRealPathSafety($directoryPath);
        $fullPath = $this->driver->getAbsolutePath(
            $realDirectoryPath . DIRECTORY_SEPARATOR,
            $path,
            $scheme
        );
        if (!$absolutePath) {
            $actualPath = $this->driver->getRealPathSafety($fullPath);
        } else {
            $actualPath = $this->driver->getRealPathSafety($path);
        }

        if (in_array($fullPath, $this->exceptionList, true)) {
            return;
        }

        foreach ($this->fileDenyList as $file) {
            $baseName = pathinfo($actualPath, PATHINFO_BASENAME);
            if (strpos($baseName, $file) !== false || preg_match('#' . "\." . $file . '#', $fullPath)) {
                throw new ValidatorException(
                    new Phrase('"%1" is not a valid file path', [$path])
                );
            }
        }
    }

    /**
     * Allow addition of new exceptions given full path
     *
     * @param string $fullPath
     */
    public function addException(string $fullPath)
    {
        if (!in_array($fullPath, $this->exceptionList)) {
            array_push($this->exceptionList, $fullPath);
        }
    }

    /**
     * Allow addition of new exceptions given full path
     *
     * @param string $fullPath
     */
    public function removeException(string $fullPath)
    {
        if (($key = array_search($fullPath, $this->exceptionList)) !== false) {
            unset($this->exceptionList[$key]);
        }
    }
}
