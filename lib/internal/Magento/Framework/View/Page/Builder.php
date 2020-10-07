<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page;

use Magento\Framework\App;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event;
use Magento\Framework\View;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

/**
 * Page Layout Builder
 */
class Builder extends View\Layout\Builder
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     */
    protected $pageLayoutReader;

    /**
     * @var BuilderInterface
     */
    private $pageLayoutBuilder;

    /**
     * @param View\LayoutInterface $layout
     * @param App\Request\Http $request
     * @param Event\ManagerInterface $eventManager
     * @param Config $pageConfig
     * @param Layout\Reader $pageLayoutReader
     * @param BuilderInterface|null $pageLayoutBuilder
     */
    public function __construct(
        View\LayoutInterface $layout,
        App\Request\Http $request,
        Event\ManagerInterface $eventManager,
        Config $pageConfig,
        Layout\Reader $pageLayoutReader,
        ?BuilderInterface $pageLayoutBuilder = null
    ) {
        parent::__construct($layout, $request, $eventManager);
        $this->pageConfig = $pageConfig;
        $this->pageLayoutReader = $pageLayoutReader;
        $this->pageLayoutBuilder = $pageLayoutBuilder ?? ObjectManager::getInstance()->get(BuilderInterface::class);
        $this->pageConfig->setBuilder($this);
    }

    /**
     * Read page layout before generation generic layout
     *
     * @return $this
     */
    protected function generateLayoutBlocks()
    {
        $this->readPageLayout();
        return parent::generateLayoutBlocks();
    }

    /**
     * Read page layout and write structure to ReadContext
     *
     * @return void
     */
    protected function readPageLayout()
    {
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $readerContext = $this->layout->getReaderContext();
            $this->pageLayoutReader->read($readerContext, $pageLayout);
        }
    }

    /**
     * Get current page layout or fallback to default
     *
     * @return string
     */
    protected function getPageLayout()
    {
        $pageLayout = $this->pageConfig->getPageLayout();

        return ($pageLayout && $this->pageLayoutBuilder->getPageLayoutsConfig()->hasPageLayout($pageLayout))
            ? $pageLayout
            : $this->layout->getUpdate()->getPageLayout();
    }
}
