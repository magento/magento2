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
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

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
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected $_themesFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $themesFactory
     * @param \Magento\Framework\App\State $appState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory,
        \Magento\Core\Model\Resource\Theme\CollectionFactory $themesFactory,
        \Magento\Framework\App\State $appState,
        array $data = array()
    ) {
        $this->_layoutProcessorFactory = $layoutProcessorFactory;
        $this->_themesFactory = $themesFactory;
        $this->_appState = $appState;
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
            $layoutUpdateParams = array('theme' => $this->_getThemeInstance($this->getTheme()));
            $designAbstractions = $this->_appState->emulateAreaCode(
                'frontend',
                array($this->_getLayoutProcessor($layoutUpdateParams), 'getAllDesignAbstractions')
            );
            $this->_addDesignAbstractionOptions($designAbstractions);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve theme instance by its identifier
     *
     * @param int $themeId
     * @return \Magento\Core\Model\Theme|null
     */
    protected function _getThemeInstance($themeId)
    {
        /** @var \Magento\Core\Model\Resource\Theme\Collection $themeCollection */
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
        $label = array();
        // Sort list of design abstractions by label
        foreach ($designAbstractions as $key => $row) {
            $label[$key] = $row['label'];
        }
        array_multisort($label, SORT_STRING, $designAbstractions);

        // Group the layout options
        $customLayouts = array();
        $pageLayouts = array();
        /** @var $layoutProcessor \Magento\Framework\View\Layout\ProcessorInterface */
        $layoutProcessor = $this->_layoutProcessorFactory->create();
        foreach ($designAbstractions as $pageTypeName => $pageTypeInfo) {
            if ($layoutProcessor->isPageLayoutDesignAbstraction($pageTypeInfo)) {
                $pageLayouts[] = array('value' => $pageTypeName, 'label' => $pageTypeInfo['label']);
            } else {
                $customLayouts[] = array('value' => $pageTypeName, 'label' => $pageTypeInfo['label']);
            }
        }
        $params = array();
        $this->addOption($customLayouts, __('Custom Layouts'), $params);
        $this->addOption($pageLayouts, __('Page Layouts'), $params);
    }
}
