<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * A chooser for container for widget instances
 *
 * @method getTheme()
 * @method getArea()
 * @method \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container setTheme($theme)
 * @method \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container setArea($area)
 */
class Container extends \Magento\Framework\View\Element\Html\Select
{
    /**#@+
     * Frontend page layouts
     */
    const PAGE_LAYOUT_1COLUMN = '1column-center';
    const PAGE_LAYOUT_2COLUMNS_LEFT = '2columns-left';
    const PAGE_LAYOUT_2COLUMNS_RIGHT = '2columns-right';
    const PAGE_LAYOUT_3COLUMNS = '3columns';
    /**#@-*/

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     */
    protected $_layoutProcessorFactory;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $_themesFactory;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themesFactory,
        array $data = []
    ) {
        $this->_layoutProcessorFactory = $layoutProcessorFactory;
        $this->_themesFactory = $themesFactory;
        parent::__construct($context, $data);
    }

    /**
     * Assign attributes for the HTML select element
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setName('block');
        $this->setClass('required-entry select');
        $this->setExtraParams(
            'onchange="WidgetInstance.loadSelectBoxByType(\'block_template\',' .
            ' this.up(\'div.group_container\'), this.value)"'
        );
    }

    /**
     * Add necessary options
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $layoutMergeParams = ['theme' => $this->_getThemeInstance($this->getTheme())];
            /** @var $layoutProcessor \Magento\Framework\View\Layout\ProcessorInterface */
            $layoutProcessor = $this->_layoutProcessorFactory->create($layoutMergeParams);
            $layoutProcessor->addPageHandles([$this->getLayoutHandle()]);
            $layoutProcessor->addPageHandles(['default']);
            $layoutProcessor->load();

            $pageLayoutProcessor = $this->_layoutProcessorFactory->create($layoutMergeParams);
            $pageLayouts = $this->getPageLayouts();
            foreach ($pageLayouts as $pageLayout) {
                $pageLayoutProcessor->addHandle($pageLayout);
            }
            $pageLayoutProcessor->load();

            $containers = array_merge($pageLayoutProcessor->getContainers(), $layoutProcessor->getContainers());
            if ($this->getAllowedContainers()) {
                foreach (array_keys($containers) as $containerName) {
                    if (!in_array($containerName, $this->getAllowedContainers())) {
                        unset($containers[$containerName]);
                    }
                }
            }
            asort($containers, SORT_STRING);

            $this->addOption('', __('-- Please Select --'));
            foreach ($containers as $containerName => $containerLabel) {
                $this->addOption($containerName, $containerLabel);
            }
        }
        return parent::_beforeToHtml();
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
     * Retrieve page layouts
     *
     * @return array
     */
    protected function getPageLayouts()
    {
        return [
            self::PAGE_LAYOUT_1COLUMN,
            self::PAGE_LAYOUT_2COLUMNS_LEFT,
            self::PAGE_LAYOUT_2COLUMNS_RIGHT,
            self::PAGE_LAYOUT_3COLUMNS,
        ];
    }
}
