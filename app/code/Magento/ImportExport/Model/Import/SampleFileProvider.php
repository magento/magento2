<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Import;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * Import sample file provider model.
 *
 * This class support only *.csv.
 */
class SampleFileProvider
{
    /**
     * Associate an import entity to its module, e.g ['entity_name' => 'module_name']
     *
     * @var array
     */
    private $samples;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param array $samples
     */
    public function __construct(
        ReadFactory $readFactory,
        ComponentRegistrar $componentRegistrar,
        array $samples = []
    ) {
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->samples = $samples;
    }

    /**
     * Returns the size for the given file associated to an import entity.
     *
     * @param string $entityName
     * @return int|null
     */
    public function getSize(string $entityName)
    {
        $directoryRead = $this->getDirectoryRead($entityName);
        $filePath = $this->getPath($entityName);
        $fileSize = $directoryRead->stat($filePath)['size'] ?? null;

        return $fileSize;
    }

    /**
     * Returns content for the given file associated to an import entity.
     *
     * @param string $entityName
     * @return string
     */
    public function getFileContents(string $entityName): string
    {
        $directoryRead = $this->getDirectoryRead($entityName);
        $filePath = $this->getPath($entityName);

        return $directoryRead->readFile($filePath);
    }

    /**
     * @param string $entityName
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPath(string $entityName): string
    {
        $directoryRead = $this->getDirectoryRead($entityName);
        $fileAbsolutePath = 'Files/Sample/' . $entityName . '.csv';
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            throw new NoSuchEntityException(__("There is no file: %file", ['file' => $filePath]));
        }

        return $filePath;
    }

    /**
     * @param string $entityName
     * @return ReadInterface
     */
    private function getDirectoryRead(string $entityName): ReadInterface
    {
        $moduleName = $this->getModuleName($entityName);
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $directoryRead = $this->readFactory->create($moduleDir);

        return $directoryRead;
    }

    /**
     * @param string $entityName
     * @return string
     * @throws NoSuchEntityException
     */
    private function getModuleName(string $entityName): string
    {
        if (!isset($this->samples[$entityName])) {
            throw new NoSuchEntityException();
        }

        return $this->samples[$entityName];
    }
}
