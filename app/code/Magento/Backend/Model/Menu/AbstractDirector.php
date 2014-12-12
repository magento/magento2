<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Menu;

abstract class AbstractDirector
{
    /**
     * Factory model
     * @var \Magento\Backend\Model\Menu\Builder\CommandFactory
     */
    protected $_commandFactory;

    /**
     * @param \Magento\Backend\Model\Menu\Builder\CommandFactory $factory
     */
    public function __construct(\Magento\Backend\Model\Menu\Builder\CommandFactory $factory)
    {
        $this->_commandFactory = $factory;
    }

    /**
     * Build menu instance
     *
     * @param array $config
     * @param \Magento\Backend\Model\Menu\Builder $builder
     * @param \Magento\Framework\Logger $logger
     * @return void
     */
    abstract public function direct(
        array $config,
        \Magento\Backend\Model\Menu\Builder $builder,
        \Magento\Framework\Logger $logger
    );
}
