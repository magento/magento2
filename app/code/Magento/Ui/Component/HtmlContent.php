<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\BlockWrapperInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class HtmlContent extends AbstractComponent implements BlockWrapperInterface
{
    const NAME = 'html_content';

    /**
     * @var BlockInterface
     */
    protected $block;

    /**
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
     * @inheritDoc
     */
    public function render()
    {
        return $this->getData('config/content') ?: $this->block->toHtml();
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration()
    {
        $configuration = parent::getConfiguration();
        if ($this->getData('wrapper/canShow') !== false) {
            if ($this->getData('isAjaxLoaded')) {
                $configuration['url'] = $this->getData('url');
            } else {
                if (!$this->getData('config/content')) { //add html block cony into cache
                    $content = $this->block->toHtml();
                    $this->addData(['config' => ['content' => $content]]);
                }

                $configuration['content'] = $this->getData('config/content');
            }
            if ($this->getData('wrapper')) {
                $configuration = array_merge($this->getData(), $this->getData('wrapper'));
            }
        }
        return $configuration;
    }
}
