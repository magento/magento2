<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * @api
 * @since 2.0.0
 */
class TemplateEnginePool
{
    /**
     * Factory
     *
     * @var TemplateEngineFactory
     * @since 2.0.0
     */
    protected $factory;

    /**
     * Template engines
     *
     * @var \Magento\Framework\View\TemplateEngineInterface[]
     * @since 2.0.0
     */
    protected $engines = [];

    /**
     * Constructor
     *
     * @param TemplateEngineFactory $factory
     * @since 2.0.0
     */
    public function __construct(TemplateEngineFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve a template engine instance by its unique name
     *
     * @param string $name
     * @return \Magento\Framework\View\TemplateEngineInterface
     * @since 2.0.0
     */
    public function get($name)
    {
        if (!isset($this->engines[$name])) {
            $this->engines[$name] = $this->factory->create($name);
        }
        return $this->engines[$name];
    }
}
