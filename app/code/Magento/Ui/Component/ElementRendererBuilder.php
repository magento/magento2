<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class ElementRendererBuilder
 */
class ElementRendererBuilder
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance class name
     *
     * @var string
     */
    protected $instanceClass = 'Magento\Ui\Component\ElementRenderer';

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create element to the render
     *
     * @param UiComponentInterface $element
     * @param array $renderData
     * @return ElementRendererInterface
     */
    public function create(UiComponentInterface $element, array $renderData)
    {
        return $this->objectManager->create($this->instanceClass, ['element' => $element, 'data' => $renderData]);
    }
}
