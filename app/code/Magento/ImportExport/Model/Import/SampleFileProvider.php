<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Import Sample File Provider model
 */
class SampleFileProvider
{
    /**
     * Associate an import entity to its module, e.g ['entity_name' => 'module_name']
     * @var array
     */
    private $samples;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param array $samples
     */
    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        array $samples = []
    ) {
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->samples = $samples;
    }

    /**
     * Returns the Size for the given file associated to an Import entity
     *
     * @param string $entityName
     * @throws NoSuchEntityException
     * @return int
     */
    public function getSize(string $entityName): int
    {
        $directoryRead = $this->getDirectoryRead($entityName);
        $filePath = $this->getPath($entityName);
        $fileSize = isset($directoryRead->stat($filePath)['size'])
            ? $directoryRead->stat($filePath)['size'] : null;

        return $fileSize;
    }

    /**
     * Returns Content for the given file associated to an Import entity
     *
     * @param string $entityName
     * @throws NoSuchEntityException
     * @return string
     */
    public function getFileContents(string $entityName): string
    {
        $directoryRead = $this->getDirectoryRead($entityName);
        $filePath = $this->getPath($entityName);

        return $directoryRead->readFile($filePath);
    }

    /**
     * @param $entityName
     * @return string $entityName
     * @throws NoSuchEntityException
     */
    private function getPath(string $entityName): string
    {
        $directoryRead = $this->getDirectoryRead($entityName);
        $moduleName = $this->getModuleName($entityName);
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $entityName . '.csv';

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
