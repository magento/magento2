<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Laminas\Validator\File\Upload;
use Laminas\Validator\ValidatorInterface;
use Magento\Framework\Exception\InputException;

class Http implements HttpInterface
{

    /**
     * Internal list of validators
     * @var array
     */
    protected $validators = [];

    /**
     * Internal list of files
     * @var array
     */
    protected $files = [];

    /**
     * TMP directory
     * @var string
     */
    protected $tmpDir;

    /**
     * Available options for file transfers
     * @var array
     */
    protected $options = [
        'ignoreNoFile'  => false,
        'useByteString' => true,
        'magicFile'     => null,
        'detectInfos'   => true,
    ];

    /**
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Constructor for Http File Transfers
     *
     * @param array $options
     * @throws InputException
     */

    public function __construct(
        array $options = [],
    ) {
        $this->options = array_merge($this->options, $options);
        $this->prepareFiles();
        $this->addValidator(new Upload());
    }
    /**
     * Checks if the files are valid
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool True if all checks are valid
     */
    public function isValid($files = null): bool
    {
        $fileContent = $this->getFileInfo($files);

        $valid = true;
        foreach ($fileContent as $file) {
            foreach ($this->validators as $validator) {
                if (!$validator->isValid($file['tmp_name'], $file)) {
                    $valid = false;
                    $this->messages += $validator->getMessages();
                }
            }
        }
        return $valid;
    }

    /**
     * Prepare the $_FILES array to match the internal syntax of one file per entry
     *
     * @return HttpInterface
     */
    protected function prepareFiles() : HttpInterface
    {
        
        $this->files = [];
        $options = $this->options;
        foreach ($_FILES as $form => $content) {
            $content['options'] = $options;
            $content['validated'] = false;
            $content['received'] = false;
            $content['filtered'] = false;
            $this->files[$form] = $content;
        }
        return $this;
    }

    /**
     * Retrieve error codes
     *
     * @return array
     */
    public function getErrors(): array
    {
        return array_keys($this->messages);
    }

    /**
     * Adds a new validator for this class
     *
     * @param string|ValidatorInterface $validator
     * @return HttpInterface
     * @throws InputException
     */
    public function addValidator(
        string|ValidatorInterface $validator
    ):HttpInterface {
        if (! $validator instanceof ValidatorInterface) {
            throw new InputException(
                'Invalid validator provided to addValidator; ' .
                'must be string or Laminas\Validator\ValidatorInterface'
            );
        }

        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Has a file been uploaded ?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isUploaded($files = null): bool
    {
        if (empty($this->files)) {
            return false;
        }
        $fileContent = $this->getFileInfo($files);
        foreach ($fileContent as $file) {
            if (empty($file['name'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve additional internal file information for files
     *
     * @param  string $file (Optional) File to get information for
     * @return mixed
     */
    public function getFileInfo($file = null): mixed
    {
        $check = [];
        if ($file !== null && isset($this->files[$file])) {
            $check[$file] = $this->files[$file];
            return $check;
        }
        return $this->files;
    }
}
