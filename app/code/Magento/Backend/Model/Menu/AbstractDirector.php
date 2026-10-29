<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

/**
 * @api
 * @since 100.0.2
 */
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
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    abstract public function direct(
        array $config,
        \Magento\Backend\Model\Menu\Builder $builder,
        \Psr\Log\LoggerInterface $logger
    );
}
