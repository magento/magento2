<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\PageLayout\ConfigFactory as PageLayoutConfigFactory;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated as PageLayoutFileCollector;

/**
 * Widget Instance design abstractions chooser
 *
 * @method getArea()
 * @method getTheme()
 */
class DesignAbstraction extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     */
    protected $_layoutProcessorFactory;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $_themesFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var PageLayoutConfigFactory
     */
    private $pageLayoutConfigFactory;

    /**
     * @var PageLayoutFileCollector
     */
    private $pageLayoutFileCollector;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themesFactory
     * @param \Magento\Framework\App\State $appState
     * @param array $data
     * @param PageLayoutConfigFactory|null $pageLayoutConfigFactory
     * @param PageLayoutFileCollector|null $pageLayoutFileCollector
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themesFactory,
        \Magento\Framework\App\State $appState,
        array $data = [],
        ?PageLayoutConfigFactory $pageLayoutConfigFactory = null,
        ?PageLayoutFileCollector $pageLayoutFileCollector = null
    ) {
        $this->_layoutProcessorFactory = $layoutProcessorFactory;
        $this->_themesFactory = $themesFactory;
        $this->_appState = $appState;
        $this->pageLayoutConfigFactory = $pageLayoutConfigFactory
            ?? ObjectManager::getInstance()->get(PageLayoutConfigFactory::class);
        $this->pageLayoutFileCollector = $pageLayoutFileCollector
            ?? ObjectManager::getInstance()->get(PageLayoutFileCollector::class);
        parent::__construct($context, $data);
    }

    /**
     * Add necessary options
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('-- Please Select --'));
            $theme = $this->_getThemeInstance($this->getTheme());
            $layoutUpdateParams = ['theme' => $theme];
            $designAbstractions = $this->_appState->emulateAreaCode(
                'frontend',
                [$this->_getLayoutProcessor($layoutUpdateParams), 'getAllDesignAbstractions']
            );
            if ($theme instanceof ThemeInterface) {
                $designAbstractions += $this->getPageLayoutDesignAbstractions($theme);
            }
            $this->_addDesignAbstractionOptions($designAbstractions);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Page layouts declared in the theme's layouts.xml, described as page layout design abstractions
     *
     * @param ThemeInterface $theme
     * @return array
     */
    private function getPageLayoutDesignAbstractions(ThemeInterface $theme): array
    {
        $configFiles = $this->pageLayoutFileCollector->getFilesContent($theme, 'layouts.xml');
        if (!$configFiles) {
            return [];
        }
        $pageLayoutsConfig = $this->pageLayoutConfigFactory->create(['configFiles' => $configFiles]);
        $result = [];
        foreach ($pageLayoutsConfig->getPageLayouts() as $name => $label) {
            $result[$name] = [
                'name' => (string)$name,
                'label' => (string)__($label),
                'design_abstraction' => Merge::DESIGN_ABSTRACTION_PAGE_LAYOUT,
            ];
        }
        return $result;
    }

    /**
     * Retrieve theme instance by its identifier
     *
     * @param int $themeId
     * @return \Magento\Theme\Model\Theme|null
     */
    protected function _getThemeInstance($themeId)
    {
        /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection */
        $themeCollection = $this->_themesFactory->create();
        return $themeCollection->getItemById($themeId);
    }

    /**
     * Retrieve new layout merge model instance
     *
     * @param array $arguments
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    protected function _getLayoutProcessor(array $arguments)
    {
        return $this->_layoutProcessorFactory->create($arguments);
    }

    /**
     * Add design abstractions information to the options
     *
     * @param array $designAbstractions
     * @return void
     */
    protected function _addDesignAbstractionOptions(array $designAbstractions)
    {
        $label = [];
        // Sort list of design abstractions by label
        foreach ($designAbstractions as $key => $row) {
            $label[$key] = $row['label'];
            $designAbstractions[$key]['name'] = $row['name'] ?? (string)$key;
        }
        array_multisort($label, SORT_STRING, $designAbstractions);

        // Group the layout options
        $customLayouts = [];
        $pageLayouts = [];
        /** @var $layoutProcessor \Magento\Framework\View\Layout\ProcessorInterface */
        $layoutProcessor = $this->_layoutProcessorFactory->create();
        foreach ($designAbstractions as $pageTypeInfo) {
            if ($layoutProcessor->isPageLayoutDesignAbstraction($pageTypeInfo)) {
                $pageLayouts[] = ['value' => $pageTypeInfo['name'], 'label' => $pageTypeInfo['label']];
            } else {
                $customLayouts[] = ['value' => $pageTypeInfo['name'], 'label' => $pageTypeInfo['label']];
            }
        }
        $params = [];
        $this->addOption($customLayouts, __('Custom Layouts'), $params);
        $this->addOption($pageLayouts, __('Page Layouts'), $params);
    }
}
