<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\App;
use Magento\Framework\Event;
use Magento\Framework\Profiler;
use Magento\Framework\View;

/**
 * Class Builder
 */
class Builder implements BuilderInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var bool
     */
    protected $isBuilt = false;

    /**
     * @param View\LayoutInterface $layout
     * @param App\Request\Http $request
     * @param Event\ManagerInterface $eventManager
     */
    public function __construct(
        View\LayoutInterface $layout,
        App\Request\Http $request,
        Event\ManagerInterface $eventManager
    ) {
        $this->layout = $layout;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->layout->setBuilder($this);
    }

    /**
     * Build layout structure
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function build()
    {
        if (!$this->isBuilt) {
            $this->isBuilt = true;
            $this->loadLayoutUpdates();
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
        }
        return $this->layout;
    }

    /**
     * Load layout updates
     *
     * @return $this
     */
    protected function loadLayoutUpdates()
    {
        Profiler::start('LAYOUT');
        /* dispatch event for adding handles to layout update */
        $this->eventManager->dispatch(
            'layout_load_before',
            ['full_action_name' => $this->request->getFullActionName(), 'layout' => $this->layout]
        );
        Profiler::start('layout_load');

        /* load layout updates by specified handles */
        $this->layout->getUpdate()->load();

        Profiler::stop('layout_load');
        Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Generate layout xml
     *
     * @return $this
     */
    protected function generateLayoutXml()
    {
        Profiler::start('LAYOUT');
        Profiler::start('layout_generate_xml');

        /* generate xml from collected text updates */
        $this->layout->generateXml();

        Profiler::stop('layout_generate_xml');
        Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Generate layout blocks
     *
     * @return $this
     */
    protected function generateLayoutBlocks()
    {
        $this->beforeGenerateBlock();

        Profiler::start('LAYOUT');
        /* dispatch event for adding xml layout elements */
        $this->eventManager->dispatch(
            'layout_generate_blocks_before',
            ['full_action_name' => $this->request->getFullActionName(), 'layout' => $this->layout]
        );
        Profiler::start('layout_generate_blocks');

        /* generate blocks from xml layout */
        $this->layout->generateElements();

        Profiler::stop('layout_generate_blocks');
        $this->eventManager->dispatch(
            'layout_generate_blocks_after',
            ['full_action_name' => $this->request->getFullActionName(), 'layout' => $this->layout]
        );
        Profiler::stop('LAYOUT');

        $this->afterGenerateBlock();

        return $this;
    }

    /**
     * @return $this
     */
    protected function beforeGenerateBlock()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function afterGenerateBlock()
    {
        return $this;
    }
}
