<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\View;

/**
 * A generic layout response can be used for rendering any kind of layout
 * So it comprises a response body from the layout elements it has and sets it to the HTTP response
 */
class Layout extends AbstractResult
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     */
    protected $layoutBuilderFactory;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool
     */
    protected $layoutReaderPool;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     *
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param View\Layout\ReaderPool $layoutReaderPool
     * @param Framework\Translate\InlineInterface $translateInline
     * @param View\Layout\BuilderFactory $layoutBuilderFactory
     * @param View\Layout\GeneratorPool $generatorPool
     * @param bool $isIsolated
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        $isIsolated = false
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->layoutBuilderFactory = $layoutBuilderFactory;
        $this->layoutReaderPool = $layoutReaderPool;
        $this->eventManager = $context->getEventManager();
        $this->request = $context->getRequest();
        $this->translateInline = $translateInline;
        // TODO Shared layout object will be deleted in MAGETWO-28359
        $this->layout = $isIsolated
            ? $this->layoutFactory->create(['reader' => $this->layoutReaderPool, 'generatorPool' => $generatorPool])
            : $context->getLayout();
        $this->layout->setGeneratorPool($generatorPool);
        $this->initLayoutBuilder();
    }

    /**
     * Create layout builder
     *
     * @return void
     */
    protected function initLayoutBuilder()
    {
        $this->layoutBuilderFactory->create(View\Layout\BuilderFactory::TYPE_LAYOUT, ['layout' => $this->layout]);
    }

    /**
     * Get layout instance for current page
     *
     * @return \Magento\Framework\View\Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return $this
     */
    public function addDefaultHandle()
    {
        $this->addHandle($this->getDefaultLayoutHandle());
        return $this;
    }

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function getDefaultLayoutHandle()
    {
        return strtolower($this->request->getFullActionName());
    }

    /**
     * @param string|string[] $handleName
     * @return $this
     */
    public function addHandle($handleName)
    {
        $this->getLayout()->getUpdate()->addHandle($handleName);
        return $this;
    }

    /**
     * Add update to merge object
     *
     * @param string $update
     * @return $this
     */
    public function addUpdate($update)
    {
        $this->getLayout()->getUpdate()->addUpdate($update);
        return $this;
    }

    /**
     * Render current layout
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        \Magento\Framework\Profiler::start('LAYOUT');
        \Magento\Framework\Profiler::start('layout_render');

        $this->applyHttpHeaders($response);
        $this->render($response);

        $this->eventManager->dispatch('layout_render_before');
        $this->eventManager->dispatch('layout_render_before_' . $this->request->getFullActionName());
        \Magento\Framework\Profiler::stop('layout_render');
        \Magento\Framework\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Render current layout
     *
     * @param ResponseInterface $response
     * @return $this
     */
    protected function render(ResponseInterface $response)
    {
        $output = $this->layout->getOutput();
        $this->translateInline->processResponseBody($output);
        $response->appendBody($output);
        return $this;
    }
}
