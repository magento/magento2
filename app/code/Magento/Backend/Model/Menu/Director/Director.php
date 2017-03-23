<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Model\Menu\Director;

class Director extends \Magento\Backend\Model\Menu\AbstractDirector
{
    /**
     * Log message patterns
     *
     * @var array
     */
    protected $_messagePatterns = ['update' => 'Item %s was updated', 'remove' => 'Item %s was removed'];

    /**
     * Get command object
     *
     * @param array $data command params
     * @param \Psr\Log\LoggerInterface $logger
     * @return \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected function _getCommand($data, $logger)
    {
        $command = $this->_commandFactory->create($data['type'], ['data' => $data]);
        if (isset($this->_messagePatterns[$data['type']])) {
            $logger->info(
                sprintf($this->_messagePatterns[$data['type']], $command->getId())
            );
        }
        return $command;
    }

    /**
     * Build menu instance
     *
     * @param array $config
     * @param \Magento\Backend\Model\Menu\Builder $builder
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function direct(array $config, \Magento\Backend\Model\Menu\Builder $builder, \Psr\Log\LoggerInterface $logger)
    {
        foreach ($config as $data) {
            $builder->processCommand($this->_getCommand($data, $logger));
        }
    }
}
