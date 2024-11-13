<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner;
use Magento\Setup\Module\Di\Code\Scanner\PhpScanner;

class ApplicationCodeGenerator implements OperationInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @var PhpScanner
     */
    private $phpScanner;

    /**
     * @var DirectoryScanner
     */
    private $directoryScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param PhpScanner $phpScanner
     * @param DirectoryScanner $directoryScanner
     * @param array $data
     */
    public function __construct(
        ClassesScanner $classesScanner,
        PhpScanner $phpScanner,
        DirectoryScanner $directoryScanner,
        $data = []
    ) {
        $this->data = $data;
        $this->classesScanner = $classesScanner;
        $this->phpScanner = $phpScanner;
        $this->directoryScanner = $directoryScanner;
    }

    /**
     * @inheritdoc
     */
    public function doOperation()
    {
        if (array_diff(array_keys($this->data), ['filePatterns', 'paths', 'excludePatterns'])
            !== array_diff(['filePatterns', 'paths', 'excludePatterns'], array_keys($this->data))) {
            return;
        }

        foreach ($this->data['paths'] as $paths) {
            if (!is_array($paths)) {
                $paths = (array)$paths;
            }

            $files = $this->getFiles($paths);

            $entities = $this->phpScanner->collectEntities($files['php'] ?? []);
            foreach ($entities as $entityName) {
                class_exists($entityName);
            }
        }
    }

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName()
    {
        return 'Application code generator';
    }

    /**
     * Get list if files.
     *
     * @param string[] $paths
     * @return array
     * @throws FileSystemException
     */
    private function getFiles(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $this->classesScanner->getList($path);

            $files[] = $this->directoryScanner->scan(
                $path,
                $this->data['filePatterns'],
                $this->data['excludePatterns']
            );
        }

        return array_merge_recursive([], ...$files);
    }
}
