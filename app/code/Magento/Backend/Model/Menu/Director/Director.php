<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
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
     * @param \Magento\Framework\Logger $logger
     * @return \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected function _getCommand($data, $logger)
    {
        $command = $this->_commandFactory->create($data['type'], ['data' => $data]);
        if (isset($this->_messagePatterns[$data['type']])) {
            $logger->logDebug(
                sprintf($this->_messagePatterns[$data['type']], $command->getId()),
                \Magento\Backend\Model\Menu::LOGGER_KEY
            );
        }
        return $command;
    }

    /**
     * Build menu instance
     *
     * @param array $config
     * @param \Magento\Backend\Model\Menu\Builder $builder
     * @param \Magento\Framework\Logger $logger
     * @return void
     */
    public function direct(array $config, \Magento\Backend\Model\Menu\Builder $builder, \Magento\Framework\Logger $logger)
    {
        foreach ($config as $data) {
            $builder->processCommand($this->_getCommand($data, $logger));
        }
    }
}
