<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;

interface ImageContentUploaderInterface extends ImageProcessorInterface
{
    public const CASE_SENSITIVE = 1;
    public const PATH_DISPERSION = 2;
    public const RENAME_IF_EXIST = 4;

    /**
     * Move image content to a temp directory.
     *
     * @param ImageContentInterface $imageContent
     * @param bool $validate
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function saveToTmpDir(
        ImageContentInterface $imageContent,
        bool $validate = true
    ): string;

    /**
     * Move image content from temp to the specified directory.
     *
     * @param ImageContentInterface $imageContent
     * @param string $tmpFileName
     * @param WriteInterface $destinationDirectory
     * @param string|null $destinationPath
     * @param string|null $fileName
     * @param int $flags Flags is a bitmask that controls the operations that can be performed on the file.
     * The default value is 0 meaning self::CASE_SENSITIVE | self::PATH_DISPERSION | self::RENAME_IF_EXIST.
     * @return string|null
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function moveFromTmpDir(
        ImageContentInterface $imageContent,
        string $tmpFileName,
        WriteInterface $destinationDirectory,
        ?string $destinationPath = null,
        ?string $fileName = null,
        int $flags = 0
    ): ?string;
}
