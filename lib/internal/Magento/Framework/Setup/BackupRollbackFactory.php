<?php
/**
 * Factory for Acl resource
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupRollbackFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create and return BackupRollback
     *
     * @param OutputInterface|LoggerInterface $outputLogger
     * @return BackupRollback
     */
    public function create($outputLogger)
    {
        if ($outputLogger instanceof OutputInterface) {
            $log = $this->_objectManager->create('Magento\Framework\Setup\ConsoleLogger', ['output' => $outputLogger]);
        } else {
            $log = $outputLogger;
        }
        return $this->_objectManager->create('Magento\Framework\Setup\BackupRollback', ['log' => $log]);
    }
}
