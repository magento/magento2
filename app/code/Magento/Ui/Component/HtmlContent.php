<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class BlockWrapper
 */
class HtmlContent extends AbstractComponent
{
    const NAME = 'html_content';

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
        parent::__construct($context, $components, $data);
        $this->block = $block;
        $this->block->setLayout($context->getPageLayout());
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = (array)$this->getData('config');
        $config['content'] = $this->block->toHtml();
        $this->setData('config', $config);
        parent::prepare();
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
