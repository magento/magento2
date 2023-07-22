<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Framework\App\State;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;
use Magento\Theme\Model\Theme;

/**
 * Widget Instance design abstractions chooser
 *
 * @method getArea()
 * @method getTheme()
 */
class DesignAbstraction extends Select
{
    /**
     * @var ProcessorFactory
     */
    protected $_layoutProcessorFactory;

    /**
     * @var CollectionFactory
     */
    protected $_themesFactory;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @param Context $context
     * @param ProcessorFactory $layoutProcessorFactory
     * @param CollectionFactory $themesFactory
     * @param State $appState
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProcessorFactory $layoutProcessorFactory,
        CollectionFactory $themesFactory,
        State $appState,
        array $data = []
    ) {
        $this->_layoutProcessorFactory = $layoutProcessorFactory;
        $this->_themesFactory = $themesFactory;
        $this->_appState = $appState;
        parent::__construct($context, $data);
    }

    /**
     * Add necessary options
     *
     * @return AbstractBlock
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('-- Please Select --'));
            $layoutUpdateParams = ['theme' => $this->_getThemeInstance($this->getTheme())];
            $designAbstractions = $this->_appState->emulateAreaCode(
                'frontend',
                [$this->_getLayoutProcessor($layoutUpdateParams), 'getAllDesignAbstractions']
            );
            $this->_addDesignAbstractionOptions($designAbstractions);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve theme instance by its identifier
     *
     * @param int $themeId
     * @return Theme|null
     */
    protected function _getThemeInstance($themeId)
    {
        /** @var Collection $themeCollection */
        $themeCollection = $this->_themesFactory->create();
        return $themeCollection->getItemById($themeId);
    }

    /**
     * Retrieve new layout merge model instance
     *
     * @param array $arguments
     * @return ProcessorInterface
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
        }
        array_multisort($label, SORT_STRING, $designAbstractions);

        // Group the layout options
        $customLayouts = [];
        $pageLayouts = [];
        /** @var $layoutProcessor ProcessorInterface */
        $layoutProcessor = $this->_layoutProcessorFactory->create();
        foreach ($designAbstractions as $pageTypeName => $pageTypeInfo) {
            if ($layoutProcessor->isPageLayoutDesignAbstraction($pageTypeInfo)) {
                $pageLayouts[] = ['value' => $pageTypeName, 'label' => $pageTypeInfo['label']];
            } else {
                $customLayouts[] = ['value' => $pageTypeName, 'label' => $pageTypeInfo['label']];
            }
        }
        $params = [];
        $this->addOption($customLayouts, __('Custom Layouts'), $params);
        $this->addOption($pageLayouts, __('Page Layouts'), $params);
    }
}
