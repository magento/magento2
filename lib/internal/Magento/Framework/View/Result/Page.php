<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\Framework;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\View;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Layout\BuilderFactory;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Page\Config\RendererInterface as PageConfigRendererInterface;
use Magento\Framework\View\Page\Config\RendererFactory as PageConfigRendererFactory;
use Magento\Framework\View\Page\Layout\Reader as PageLayoutReader;
use Magento\Framework\App\RequestInterface as AppRequestInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\FileSystem as ViewFileSystem;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\EntitySpecificHandlesList;

/**
 * Class Page represents a "page" result that encapsulates page type, page configuration
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
 * @since 100.0.2
 */
class Page extends Layout
{
    /**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var PageConfigRendererInterface
     */
    protected $pageConfigRenderer;

    /**
     * @var PageConfigRendererFactory
     */
    protected $pageConfigRendererFactory;

    /**
     * @var PageLayoutReader
     */
    protected $pageLayoutReader;

    /**
     * @var ViewFileSystem
     */
    protected $viewFileSystem;

    /**
     * @var array
     */
    protected $viewVars;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var AppRequestInterface
     */
    protected $request;

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;

    /**
     * Constructor
     *
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param ReaderPool $layoutReaderPool
     * @param InlineInterface $translateInline
     * @param BuilderFactory $layoutBuilderFactory
     * @param GeneratorPool $generatorPool
     * @param PageConfigRendererFactory $pageConfigRendererFactory
     * @param PageLayoutReader $pageLayoutReader
     * @param string $template
     * @param bool $isIsolated
     * @param EntitySpecificHandlesList $entitySpecificHandlesList
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        ReaderPool $layoutReaderPool,
        InlineInterface $translateInline,
        BuilderFactory $layoutBuilderFactory,
        GeneratorPool $generatorPool,
        PageConfigRendererFactory $pageConfigRendererFactory,
        PageLayoutReader $pageLayoutReader,
        $template,
        $isIsolated = false,
        ?EntitySpecificHandlesList $entitySpecificHandlesList = null
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
            ?: ObjectManager::getInstance()->get(EntitySpecificHandlesList::class);
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
     */
    protected function initPageConfigReader()
    {
        $this->pageConfigRenderer = $this->pageConfigRendererFactory->create(['pageConfig' => $this->pageConfig]);
    }

    /**
     * Create layout builder
     *
     * @return void
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
     */
    public function addDefaultHandle()
    {
        $this->addHandle('default');
        return parent::addDefaultHandle();
    }

    /**
     * Return page configuration
     *
     * @return PageConfig
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
     */
    public function addPageLayoutHandles(array $parameters = [], $defaultHandle = null, $entitySpecific = true)
    {
        $handle = $defaultHandle ?: $this->getDefaultLayoutHandle();
        $pageHandles = [$handle];
        foreach ($parameters as $key => $value) {
            $handle = $value['handle'] ?? $handle;
            $value = $value['value'] ?? $value;
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
     * Render the page.
     *
     * {@inheritdoc}
     *
     * @param HttpResponseInterface $response The HTTP response object.
     * @return $this
     * @throws \Exception If the template file is not found.
     */
    protected function render(HttpResponseInterface $response)
    {
        $this->pageConfig->publicBuild();
        if ($this->getPageLayout()) {
            $config = $this->getConfig();
            $this->addDefaultBodyClasses();
            $addCritical = $this->getLayout()->getBlock('head.critical');
            $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
            $requireJs = $this->getLayout()->getBlock('require.js');
            $this->assign([
                'requireJs' => $requireJs ? $requireJs->toHtml() : null,
                'headContent' => $this->pageConfigRenderer->renderHeadContent(),
                'headCritical' => $addCritical ? $addCritical->toHtml() : null,
                'headAssets' => $this->pageConfigRenderer->renderHeadAssets(),
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
     * Get the page layout.
     *
     * @return string The page layout.
     */
    protected function getPageLayout()
    {
        return $this->pageConfig->getPageLayout() ?: $this->getLayout()->getUpdate()->getPageLayout();
    }

    /**
     * Assign variable
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
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
