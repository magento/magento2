<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Menu\Director;

class Director extends \Magento\Backend\Model\Menu\AbstractDirector
{
    /**
     * Log message patterns
     *
     * @var array
     */
    protected $_messagePatterns = array('update' => 'Item %s was updated', 'remove' => 'Item %s was removed');

    /**
     * Get command object
     *
     * @param array $data command params
     * @param \Magento\Framework\Logger $logger
     * @return \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected function _getCommand($data, $logger)
    {
        $command = $this->_commandFactory->create($data['type'], array('data' => $data));
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
