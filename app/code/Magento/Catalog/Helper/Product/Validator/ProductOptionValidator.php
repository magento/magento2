<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product\Validator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

/**
 * Product options validator
 */
class ProductOptionValidator extends AbstractHelper
{
    /**
     * Quote custom option path
     */
    private const QUOTE_PATH = 'custom_options/quote';

    /**
     * Order custom option path
     */
    private const ORDER_PATH = 'custom_options/order';

    /**
     * @param Context $context
     * @param MaliciousCode $maliciousCode
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        private readonly MaliciousCode $maliciousCode,
        private readonly Filesystem $filesystem
    ) {
        parent::__construct($context);
    }

    /**
     * Validate file paths for custom options
     *
     * @param array $checkPaths [quotePath, orderPath]
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateOptionsFilePath(array $checkPaths): void
    {
        if (count($checkPaths) < 2) {
            return;
        }

        [$quotePath, $orderPath] = $checkPaths;

        if (!is_string($quotePath) || !is_string($orderPath)) {
            throw new LocalizedException(__("Invalid file path."));
        }

        if (!$this->maliciousCode->isValidPath($quotePath) || !$this->maliciousCode->isValidPath($orderPath)) {
            throw new LocalizedException(__("Invalid file path."));
        }

        $this->validateFilePath($quotePath, $orderPath);

        if (strpos($quotePath, self::QUOTE_PATH . DIRECTORY_SEPARATOR) !== 0) {
            throw new LocalizedException(__("Invalid quote path."));
        }

        if (strpos($orderPath, self::ORDER_PATH . DIRECTORY_SEPARATOR) !== 0) {
            throw new LocalizedException(__("Invalid order path."));
        }

        $expectedOrderPath = str_replace(self::QUOTE_PATH, self::ORDER_PATH, $quotePath);

        if ($orderPath !== $expectedOrderPath) {
            throw new LocalizedException(__("Invalid file path."));
        }
    }

    /**
     * Validate file paths
     *
     * @param string $quotePath
     * @param string $orderPath
     * @return void
     * @throws LocalizedException
     */
    private function validateFilePath(string $quotePath, string $orderPath): void
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $allowedDirectory = rtrim($mediaDirectory->getAbsolutePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $driver = $mediaDirectory->getDriver();

        $quoteFullPath = $this->buildFullPath($quotePath, $allowedDirectory);
        $quoteResolved = $driver->getRealPath($quoteFullPath);
        if ($quoteResolved === false) {
            throw new LocalizedException(__("Invalid file path."));
        }
        $this->validatePathWithinAllowedDirectory($quoteResolved, $allowedDirectory);

        $orderFullPath = $this->buildFullPath($orderPath, $allowedDirectory);
        $orderResolved = $driver->getRealPath($orderFullPath);

        if ($orderResolved !== false) {
            $this->validatePathWithinAllowedDirectory($orderResolved, $allowedDirectory);
        } else {
            $parentDir = $driver->getParentDirectory($orderFullPath);
            $parentResolved = $driver->getRealPath($parentDir);
            if ($parentResolved !== false) {
                $this->validatePathWithinAllowedDirectory($parentResolved, $allowedDirectory);
            }
        }
    }

    /**
     * Build path from relative path and allowed directory
     *
     * @param string $path
     * @param string $allowedDirectory
     * @return string
     */
    private function buildFullPath(string $path, string $allowedDirectory): string
    {
        return $allowedDirectory . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Validate allowed directory
     *
     * @param string $resolvedPath
     * @param string $allowedDirectory
     * @return void
     * @throws LocalizedException
     */
    private function validatePathWithinAllowedDirectory(string $resolvedPath, string $allowedDirectory): void
    {
        if (!str_starts_with($resolvedPath, $allowedDirectory)) {
            throw new LocalizedException(__("Invalid file path."));
        }
    }
}
