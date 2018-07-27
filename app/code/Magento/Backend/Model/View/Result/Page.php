<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Result;

use Magento\Framework\Translate;
use Magento\Framework\View;

class Page extends View\Result\Page
{
    /**
     * Constructor
     *
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param View\Layout\ReaderPool $layoutReaderPool
     * @param Translate\InlineInterface $translateInline
     * @param View\Layout\BuilderFactory $layoutBuilderFactory
     * @param View\Layout\GeneratorPool $generatorPool
     * @param View\Page\Config\RendererFactory $pageConfigRendererFactory
     * @param View\Page\Layout\Reader $pageLayoutReader
     * @param string $template
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
        $template
    ) {
        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
            $pageConfigRendererFactory,
            $pageLayoutReader,
            $template
        );
    }

    /**
     * Define active menu item in menu block
     *
     * @param string $itemId current active menu item
     * @return $this
     */
    public function setActiveMenu($itemId)
    {
        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menuBlock = $this->layout->getBlock('menu');
        $menuBlock->setActive($itemId);
        $parents = $menuBlock->getMenuModel()->getParentItems($itemId);
        foreach ($parents as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            $this->getConfig()->getTitle()->prepend($item->getTitle());
        }
        return $this;
    }

    /**
     * Add link to breadcrumb block
     *
     * @param string $label
     * @param string $title
     * @param string|null $link
     * @return $this
     */
    public function addBreadcrumb($label, $title, $link = null)
    {
        /** @var \Magento\Backend\Block\Widget\Breadcrumbs $block */
        $block = $this->layout->getBlock('breadcrumbs');
        if ($block) {
            $block->addLink($label, $title, $link);
        }
        return $this;
    }

    /**
     * Add content to content section
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function addContent(View\Element\AbstractBlock $block)
    {
        return $this->moveBlockToContainer($block, 'content');
    }

    /**
     * Add block to left container
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function addLeft(View\Element\AbstractBlock $block)
    {
        return $this->moveBlockToContainer($block, 'left');
    }

    /**
     * Add javascript to head
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function addJs(View\Element\AbstractBlock $block)
    {
        return $this->moveBlockToContainer($block, 'js');
    }

    /**
     * Set specified block as an anonymous child to specified container
     *
     * The block will be moved to the container from previous parent after all other elements
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param string $containerName
     * @return $this
     */
    protected function moveBlockToContainer(View\Element\AbstractBlock $block, $containerName)
    {
        $this->layout->setChild($containerName, $block->getNameInLayout(), '');
        return $this;
    }
}
