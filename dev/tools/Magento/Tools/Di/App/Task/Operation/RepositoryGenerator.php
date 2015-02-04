<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Tools\Di\Code\Scanner;
use Magento\Tools\Di\Code\Reader\ClassesScanner;

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
        if (array_diff(array_keys($this->data), ['filePatterns', 'path'])
            !== array_diff(['filePatterns', 'path'], array_keys($this->data))) {
            return;
        }

        $this->classesScanner->getList($this->data['path']);

        $files = $this->directoryScanner->scan($this->data['path'], $this->data['filePatterns']);
        $this->repositoryScanner->setUseAutoload(false);
        $repositories = $this->repositoryScanner->collectEntities($files['di']);
        foreach ($repositories as $entityName) {
            class_exists($entityName);
        }
    }
}
