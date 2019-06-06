<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Code;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Exception\FileSystemException;

class Generator
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Io
     */
    private $io;

    /**
     * @var GetSourceCode
     */
    private $getSourceCode;

    /**
     * Generator constructor.
     * @param DirectoryList $directoryList
     * @param GetSourceCode $getSourceCode
     * @param Io|null $io
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directoryList,
        GetSourceCode $getSourceCode,
        Io $io = null
    ) {
        if ($io !== null) {
            $this->io = $io;
        } else {
            $this->io = ObjectManager::getInstance()->create(
                Io::class,
                [
                    'generationDirectory' => $directoryList->getPath(DirectoryList::GENERATED_CODE),
                ]
            );
        }

        $this->getSourceCode = $getSourceCode;
    }

    /**
     * Add error message
     *
     * @param string $message
     * @return void
     */
    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * @param string $className
     * @return bool
     */
    private function validateData(string $className): bool
    {
        $resultDir = $this->io->getResultFileDirectory($className);

        if (!$this->io->makeResultFileDirectory($className) && !$this->io->fileExists($resultDir)
        ) {
            $this->addError('Can\'t create directory ' . $resultDir . '.');
            return false;
        }

        return true;
    }

    /**
     * List of occurred generation errors
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Generation template method
     *
     * @param string $className
     * @return string|null
     */
    public function generate(string $className): ?string
    {
        try {
            if ($this->validateData($className)) {
                $sourceCode = $this->getSourceCode->execute($className);
                if ($sourceCode) {
                    $fileName = $this->io->generateResultFileName($className);
                    $this->io->writeResultFile($fileName, $sourceCode);
                    return $fileName;
                }

                $this->addError('Can\'t generate source code.');
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        return null;
    }
}
