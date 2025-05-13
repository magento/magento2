<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Menu\Director;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu\Builder\AbstractCommand;
use Psr\Log\LoggerInterface;

/**
 * @api
 * @since 100.0.2
 */
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
     * @param LoggerInterface $logger
     * @return AbstractCommand
     */
    protected function _getCommand($data, $logger)
    {
        $command = $this->_commandFactory->create($data['type'], ['data' => $data]);
        if (isset($this->_messagePatterns[$data['type']])) {
            $logger->debug(
                sprintf($this->_messagePatterns[$data['type']], $command->getId())
            );
        }
        return $command;
    }

    /**
     * Build menu instance
     *
     * @param array $config
     * @param Builder $builder
     * @param LoggerInterface $logger
     * @return void
     */
    public function direct(
        array $config,
        Builder $builder,
        LoggerInterface $logger
    ) {
        foreach ($config as $data) {
            $builder->processCommand($this->_getCommand($data, $logger));
        }
    }
}
