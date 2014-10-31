<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\App;
use Magento\Framework\View;
use Magento\Framework\Event;
use Magento\Framework\Profiler;

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
            'controller_action_layout_load_before',
            array('full_action_name' => $this->request->getFullActionName(), 'layout' => $this->layout)
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
     * TODO: Restore action flag functionality to have ability to turn off event dispatching
     *
     * @return $this
     */
    protected function generateLayoutBlocks()
    {
        $this->beforeGenerateBlock();

        Profiler::start('LAYOUT');
        /* dispatch event for adding xml layout elements */
        $this->eventManager->dispatch(
            'controller_action_layout_generate_blocks_before',
            array('full_action_name' => $this->request->getFullActionName(), 'layout' => $this->layout)
        );
        Profiler::start('layout_generate_blocks');

        /* generate blocks from xml layout */
        $this->layout->generateElements();

        Profiler::stop('layout_generate_blocks');
        $this->eventManager->dispatch(
            'controller_action_layout_generate_blocks_after',
            array('full_action_name' => $this->request->getFullActionName(), 'layout' => $this->layout)
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
