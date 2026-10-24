<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator\Address;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

/**
 * Validator for file uploads.
 */
class File
{
    /**
     * Regular expression pattern for validating file paths.
     */
    private const PATTERN_NAME = '/(?:^|\/|\\\\)('
        . '(?:\.{2}[\/\\\\]|%2e%2e%2f|%2e%2e\/|\.\.%2f|%2e%2e%5c|%2e%2e\\|\.\.%5c|'
        . '%252e%252e%255c|\.\.%255c|%2e%2e\\\\|\.\.\\\\|\.\.%5c|%c0%2e%2e%2f|%c0%2e%2e%5c|'
        . '%c0%ae%c0%ae%2f|%c0%ae%c0%ae%5c|%e0%40%ae%2e%2f|%e0%40%ae%2e%5c|%c0%2e%c0%2e%2f|'
        . '%c0%2e%c0%2e%5c)'
        . ')/';

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @param FileDriver $fileDriver
     */
    public function __construct(FileDriver $fileDriver)
    {
        $this->fileDriver = $fileDriver;
    }

    /**
     * Validate the filename and path
     *
     * @param string $fileName
     * @param string $absolutePath
     * @param string $allowedAbsolutePath
     * @return void
     * @throws LocalizedException
     */
    public function validate(string $fileName, string $absolutePath, string $allowedAbsolutePath): void
    {
        if (!$this->isValid($fileName) || !$this->isWithinAllowedDirectory($absolutePath, $allowedAbsolutePath)) {
            throw new LocalizedException(__('Invalid file path.'));
        }
    }

    /**
     * Check if the filename is valid
     *
     * @param string $fileName
     * @return bool
     */
    private function isValid(string $fileName): bool
    {
        return !preg_match(self::PATTERN_NAME, $fileName);
    }

    /**
     * Check if the path is within the allowed directory
     *
     * @param string $absolutePath
     * @param string $allowedAbsolutePath
     * @return bool
     */
    private function isWithinAllowedDirectory(string $absolutePath, string $allowedAbsolutePath): bool
    {
        $absolutePath = $this->fileDriver->getRealPath($absolutePath);
        $allowedAbsolutePath = $this->fileDriver->getRealPath($allowedAbsolutePath);
        return strpos($absolutePath, $allowedAbsolutePath) === 0;
    }
}
