<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Layout\Pool as LayoutPool;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Layout
 * @since 2.0.0
 */
class Layout extends AbstractComponent
{
    const NAME = 'layout';

    /**
     * @var LayoutPool
     * @since 2.0.0
     */
    protected $layoutPool;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $type;

    /**
     * @var LayoutInterface
     * @since 2.0.0
     */
    protected $layoutTypeObject;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $structure = [];

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param LayoutPool $layoutPool
     * @param string $type
     * @param array $components
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ContextInterface $context,
        LayoutPool $layoutPool,
        $type,
        array $components = [],
        array $data = []
    ) {
        $this->layoutPool = $layoutPool;
        $this->type = $type;
        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Register component and build layout structure
     *
     * @inheritdoc
     * @since 2.0.0
     */
    public function prepare()
    {
        $this->layoutTypeObject = $this->layoutPool->create($this->type);
        $this->structure = $this->layoutTypeObject->build($this);
        parent::prepare();
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
