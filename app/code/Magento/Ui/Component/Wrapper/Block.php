<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wrapper;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponent\BlockWrapperInterface;

/**
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class Block extends AbstractComponent implements BlockWrapperInterface
{
    const NAME = 'blockWrapper';

    /**
     * @var BlockInterface
     * @since 2.0.0
     */
    protected $block;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param BlockInterface $block
     * @param array $components
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBlock()
    {
        return $this->block;
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
     * @return string
     * @since 2.0.0
     */
    public function render()
    {
        return $this->block->toHtml();
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getConfiguration()
    {
        return array_merge(
            (array) $this->block->getData('config'),
            (array) $this->getData('config')
        );
    }
}
