<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for Template Engine
 */
class TemplateEngineFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Engines
     *
     * @var array
     */
    protected $engines;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $engines Format: array('<name>' => 'TemplateEngine\Class', ...)
     */
    public function __construct(ObjectManagerInterface $objectManager, array $engines)
    {
        $this->objectManager = $objectManager;
        $this->engines = $engines;
    }

    /**
     * Retrieve a template engine instance by its unique name
     *
     * @param string $name
     * @return TemplateEngineInterface
     * @throws \UnexpectedValueException If template engine doesn't implement the necessary interface
     * @throws \InvalidArgumentException If template engine doesn't exist
     */
    public function create($name)
    {
        if (!isset($this->engines[$name])) {
            throw new \InvalidArgumentException("Unknown template engine type: '{$name}'.");
        }
        $engineClass = $this->engines[$name];
        $engineInstance = $this->objectManager->create($engineClass);
        if (!$engineInstance instanceof \Magento\Framework\View\TemplateEngineInterface) {
            throw new \UnexpectedValueException("{$engineClass} has to implement the template engine interface.");
        }
        return $engineInstance;
    }
}
