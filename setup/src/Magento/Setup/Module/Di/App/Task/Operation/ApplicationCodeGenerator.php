<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class ApplicationCodeGenerator implements OperationInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param array $data
     */
    public function __construct(
        ClassesScanner $classesScanner,
        $data = []
    ) {
        $this->data = $data;
        $this->classesScanner = $classesScanner;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        foreach ($this->data as $path) {
            $this->classesScanner->getList($path);
        }
    }
}
