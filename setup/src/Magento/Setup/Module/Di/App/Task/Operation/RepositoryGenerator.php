<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class RepositoryGenerator implements OperationInterface
{
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
     * @var Scanner\ConfigurationScanner
     */
    private $configurationScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param Scanner\RepositoryScanner $repositoryScanner
     * @param Scanner\ConfigurationScanner $configurationScanner
     * @param array $data
     */
    public function __construct(
        ClassesScanner $classesScanner,
        Scanner\RepositoryScanner $repositoryScanner,
        \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner $configurationScanner,
        $data = []
    ) {
        $this->repositoryScanner = $repositoryScanner;
        $this->data = $data;
        $this->classesScanner = $classesScanner;
        $this->configurationScanner = $configurationScanner;
    }

    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation()
    {
        foreach ($this->data['paths'] as $path) {
            $this->classesScanner->getList($path);
        }
        $this->repositoryScanner->setUseAutoload(false);
        $files = $this->configurationScanner->scan('di.xml');
        $repositories = $this->repositoryScanner->collectEntities($files);
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
