<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class RepositoryGenerator implements OperationInterface
{
    /**
     * @var Scanner\DirectoryScanner
     */
    private $directoryScanner;

    /**
     * @var Scanner\RepositoryScanner
     */
    private $repositoryScanner;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @param Scanner\DirectoryScanner $directoryScanner
     * @param ClassesScanner $classesScanner
     * @param Scanner\RepositoryScanner $repositoryScanner
     * @param array $data
     */
    public function __construct(
        Scanner\DirectoryScanner $directoryScanner,
        ClassesScanner $classesScanner,
        Scanner\RepositoryScanner $repositoryScanner,
        $data = []
    ) {
        $this->directoryScanner = $directoryScanner;
        $this->repositoryScanner = $repositoryScanner;
        $this->data = $data;
        $this->classesScanner = $classesScanner;
    }

    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation()
    {
        if (array_diff(array_keys($this->data), ['filePatterns', 'paths', 'excludePatterns'])
            !== array_diff(['filePatterns', 'paths', 'excludePatterns'], array_keys($this->data))) {
            return;
        }

        foreach ($this->data['paths'] as $path) {
            $this->classesScanner->getList($path);
        }
        $this->repositoryScanner->setUseAutoload(false);
        $files = [];
        foreach ($this->data['paths'] as $path) {
            $files = array_merge_recursive(
                $files,
                $this->directoryScanner->scan($path, $this->data['filePatterns'], $this->data['excludePatterns'])
            );
        }
        $repositories = $this->repositoryScanner->collectEntities($files['di']);
        foreach ($repositories as $entityName) {
            class_exists($entityName);
        }
    }

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName()
    {
        return 'Repositories code generation';
    }
}
