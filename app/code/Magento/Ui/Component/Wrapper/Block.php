<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wrapper;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class Block
 */
class Block extends AbstractComponent
{
    const NAME = 'blockWrapper';

    /**
     * @var BlockInterface
     */
    protected $block;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param BlockInterface $block
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BlockInterface $block,
        array $components = [],
        array $data = []
    ) {
        $this->block = $block;
        parent::__construct($context, $components, $data);
    }

    /**
     * Get wrapped block
     *
     * @return BlockInterface
     */
    public function getBlock()
    {
        return $this->block;
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
     * @return string
     */
    public function render()
    {
        return $this->block->toHtml();
    }
}
