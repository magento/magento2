<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\File;

use Laminas\Validator\ValidatorInterface;

/**
 * Interface HttpInterface
 *
 * Provides methods for validating and handling HTTP file uploads.
 */
interface HttpInterface
{

    /**
     * Validates the uploaded files.
     *
     * @param mixed $files
     * @return bool
     */
    public function isValid($files = null): bool;

    /**
     * Retrieves the list of errors encountered during validation.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Adds a validator to the validation chain.
     *
     * @param string|ValidatorInterface $validator
     * @return HttpInterface
     */
    public function addValidator(
        string|ValidatorInterface $validator
    ): HttpInterface;

    /**
     * Checks if the files have been uploaded.
     *
     * @param mixed $files
     * @return bool
     */
    public function isUploaded($files = null): bool;

    /**
     * Retrieve additional internal file information for files
     *
     * @param  string $file (Optional) File to get information for
     * @return mixed
     */
    public function getFileInfo($file = null): mixed;
}
