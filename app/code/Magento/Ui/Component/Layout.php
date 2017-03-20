<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Layout\Pool as LayoutPool;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Layout
 */
class Layout extends AbstractComponent
{
    const NAME = 'layout';

    /**
     * @var LayoutPool
     */
    protected $layoutPool;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var LayoutInterface
     */
    protected $layoutTypeObject;

    /**
     * @var array
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
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Register component and build layout structure
     *
     * @inheritdoc
     */
    public function prepare()
    {
        $this->layoutTypeObject = $this->layoutPool->create($this->type);
        $this->structure = $this->layoutTypeObject->build($this);
        parent::prepare();
    }

    /**
     * @return array
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
