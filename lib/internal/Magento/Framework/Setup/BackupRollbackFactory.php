<?php
/**
 * Factory for Acl resource
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class \Magento\Framework\Setup\BackupRollbackFactory
 *
 * @since 2.0.0
 */
class BackupRollbackFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create and return BackupRollback
     *
     * @param OutputInterface $output
     * @return BackupRollback
     * @since 2.0.0
     */
    public function create($output)
    {
        $log = $this->_objectManager->create(\Magento\Framework\Setup\ConsoleLogger::class, ['output' => $output]);
        return $this->_objectManager->create(\Magento\Framework\Setup\BackupRollback::class, ['log' => $log]);
    }
}
