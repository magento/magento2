<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\View;

/**
 * A generic layout response can be used for rendering any kind of layout
 * So it comprises a response body from the layout elements it has and sets it to the HTTP response
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 2.0.0
 */
class Layout extends AbstractResult
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     * @since 2.0.0
     */
    protected $layoutBuilderFactory;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool
     * @since 2.0.0
     */
    protected $layoutReaderPool;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     * @since 2.0.0
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function initLayoutBuilder()
    {
        $this->layoutBuilderFactory->create(View\Layout\BuilderFactory::TYPE_LAYOUT, ['layout' => $this->layout]);
    }

    /**
     * Get layout instance for current page
     *
     * @return \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getDefaultLayoutHandle()
    {
        return strtolower($this->request->getFullActionName());
    }

    /**
     * @param string|string[] $handleName
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addUpdate($update)
    {
        $this->getLayout()->getUpdate()->addUpdate($update);
        return $this;
    }

    /**
     * Render current layout
     *
     * @param HttpResponseInterface|ResponseInterface $httpResponse
     * @return $this
     * @since 2.0.0
     */
    public function renderResult(ResponseInterface $httpResponse)
    {
        \Magento\Framework\Profiler::start('LAYOUT');
        \Magento\Framework\Profiler::start('layout_render');

        $this->eventManager->dispatch('layout_render_before');
        $this->eventManager->dispatch('layout_render_before_' . $this->request->getFullActionName());

        $this->applyHttpHeaders($httpResponse);
        $this->render($httpResponse);

        \Magento\Framework\Profiler::stop('layout_render');
        \Magento\Framework\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function render(HttpResponseInterface $response)
    {
        $output = $this->layout->getOutput();
        $this->translateInline->processResponseBody($output);
        $response->appendBody($output);
        return $this;
    }
}
