<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\View;

/**
 * A "page" result that encapsulates page type, page configuration
 * and imposes certain layout handles.
 *
 * The framework convention is that there will be loaded a guaranteed handle for "all pages",
 * then guaranteed handle that corresponds to page type
 * and a guaranteed handle that stands for page layout (a wireframe of a page)
 *
 * Page result is a more specific implementation of a generic layout response
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 *
 * @api
 * @since 2.0.0
 */
class Page extends Layout
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $pageLayout;

    /**
     * @var \Magento\Framework\View\Page\Config
     * @since 2.0.0
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Config\RendererInterface
     * @since 2.0.0
     */
    protected $pageConfigRenderer;

    /**
     * @var \Magento\Framework\View\Page\Config\RendererFactory
     * @since 2.0.0
     */
    protected $pageConfigRendererFactory;

    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     * @since 2.0.0
     */
    protected $pageLayoutReader;

    /**
     * @var \Magento\Framework\View\FileSystem
     * @since 2.0.0
     */
    protected $viewFileSystem;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $viewVars;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $template;

    /**
     * @var Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $assetRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var View\EntitySpecificHandlesList
     * @since 2.2.0
     */
    private $entitySpecificHandlesList;

    /**
     * Constructor
     *
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param View\Layout\ReaderPool $layoutReaderPool
     * @param Framework\Translate\InlineInterface $translateInline
     * @param View\Layout\BuilderFactory $layoutBuilderFactory
     * @param View\Layout\GeneratorPool $generatorPool
     * @param View\Page\Config\RendererFactory $pageConfigRendererFactory
     * @param View\Page\Layout\Reader $pageLayoutReader
     * @param string $template
     * @param bool $isIsolated
     * @param View\EntitySpecificHandlesList $entitySpecificHandlesList
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
        $template,
        $isIsolated = false,
        View\EntitySpecificHandlesList $entitySpecificHandlesList = null
    ) {
        $this->request = $context->getRequest();
        $this->assetRepo = $context->getAssetRepository();
        $this->logger = $context->getLogger();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->pageConfig = $context->getPageConfig();
        $this->pageLayoutReader = $pageLayoutReader;
        $this->viewFileSystem = $context->getViewFileSystem();
        $this->pageConfigRendererFactory = $pageConfigRendererFactory;
        $this->template = $template;
        $this->entitySpecificHandlesList = $entitySpecificHandlesList
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(View\EntitySpecificHandlesList::class);
        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
            $isIsolated
        );
        $this->initPageConfigReader();
    }

    /**
     * Initialize page config reader
     *
     * @return void
     * @since 2.0.0
     */
    protected function initPageConfigReader()
    {
        $this->pageConfigRenderer = $this->pageConfigRendererFactory->create(['pageConfig' => $this->pageConfig]);
    }

    /**
     * Create layout builder
     *
     * @return void
     * @since 2.0.0
     */
    protected function initLayoutBuilder()
    {
        $this->layoutBuilderFactory->create(View\Layout\BuilderFactory::TYPE_PAGE, [
            'layout' => $this->layout,
            'pageConfig' => $this->pageConfig,
            'pageLayoutReader' => $this->pageLayoutReader
        ]);
    }

    /**
     * Set up default handles for current page
     *
     * @return $this
     * @since 2.0.0
     */
    public function initLayout()
    {
        $this->addHandle('default');
        $this->addHandle($this->getDefaultLayoutHandle());
        $update = $this->getLayout()->getUpdate();
        if ($update->isLayoutDefined()) {
            $update->removeHandle('default');
        }
        return $this;
    }

    /**
     * Add default handle
     *
     * @return $this
     * @since 2.0.0
     */
    public function addDefaultHandle()
    {
        $this->addHandle('default');
        return parent::addDefaultHandle();
    }

    /**
     * Return page configuration
     *
     * @return \Magento\Framework\View\Page\Config
     * @since 2.0.0
     */
    public function getConfig()
    {
        return $this->pageConfig;
    }

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array|null $parameters page parameters
     * @param string|null $defaultHandle
     * @param bool $entitySpecific
     * @return bool
     * @since 2.0.0
     */
    public function addPageLayoutHandles(array $parameters = [], $defaultHandle = null, $entitySpecific = true)
    {
        $handle = $defaultHandle ? $defaultHandle : $this->getDefaultLayoutHandle();
        $pageHandles = [$handle];
        foreach ($parameters as $key => $value) {
            $pageHandle = $handle . '_' . $key . '_' . $value;
            $pageHandles[] = $pageHandle;
            if ($entitySpecific) {
                $this->entitySpecificHandlesList->addHandle($pageHandle);
            }
        }
        // Do not sort array going into add page handles. Ensure default layout handle is added first.
        $this->addHandle($pageHandles);
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function render(HttpResponseInterface $response)
    {
        $this->pageConfig->publicBuild();
        if ($this->getPageLayout()) {
            $config = $this->getConfig();
            $this->addDefaultBodyClasses();
            $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
            $requireJs = $this->getLayout()->getBlock('require.js');
            $this->assign([
                'requireJs' => $requireJs ? $requireJs->toHtml() : null,
                'headContent' => $this->pageConfigRenderer->renderHeadContent(),
                'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
                'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
                'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
                'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),
                'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif'),
            ]);

            $output = $this->getLayout()->getOutput();
            $this->assign('layoutContent', $output);
            $output = $this->renderPage();
            $this->translateInline->processResponseBody($output);
            $response->appendBody($output);
        } else {
            parent::render($response);
        }
        return $this;
    }

    /**
     * Add default body classes for current page layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function addDefaultBodyClasses()
    {
        $this->pageConfig->addBodyClass($this->request->getFullActionName('-'));
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $this->pageConfig->addBodyClass('page-layout-' . $pageLayout);
        }
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getPageLayout()
    {
        return $this->pageConfig->getPageLayout() ?: $this->getLayout()->getUpdate()->getPageLayout();
    }

    /**
     * Assign variable
     *
     * @param   string|array $key
     * @param   mixed $value
     * @return  $this
     * @since 2.0.0
     */
    protected function assign($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $subKey => $subValue) {
                $this->assign($subKey, $subValue);
            }
        } else {
            $this->viewVars[$key] = $value;
        }
        return $this;
    }

    /**
     * Render page template
     *
     * @return string
     * @throws \Exception
     * @since 2.0.0
     */
    protected function renderPage()
    {
        $fileName = $this->viewFileSystem->getTemplateFileName($this->template);
        if (!$fileName) {
            throw new \InvalidArgumentException('Template "' . $this->template . '" is not found');
        }

        ob_start();
        try {
            extract($this->viewVars, EXTR_SKIP);
            include $fileName;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     * @since 2.0.0
     */
    protected function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }
}
