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
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Instance layouts chooser
 *
 * @method getArea()
 * @method getTheme()
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

class Layout extends \Magento\Core\Block\Html\Select
{
    /**
     * @var \Magento\View\Layout\ProcessorFactory
     */
    protected $_layoutProcessorFactory;

    /**
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected $_themesFactory;

    /**
     * @param \Magento\Core\Block\Context $context
     * @param \Magento\View\Layout\ProcessorFactory $layoutProcessorFactory
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $themesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Block\Context $context,
        \Magento\View\Layout\ProcessorFactory $layoutProcessorFactory,
        \Magento\Core\Model\Resource\Theme\CollectionFactory $themesFactory,
        array $data = array()
    ) {
        $this->_layoutProcessorFactory = $layoutProcessorFactory;
        $this->_themesFactory = $themesFactory;
        parent::__construct($context, $data);
    }

    /**
     * Add necessary options
     *
     * @return \Magento\Core\Block\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('-- Please Select --'));
            $layoutUpdateParams = array(
                'theme' => $this->_getThemeInstance($this->getTheme()),
            );
            $pageTypes = array();
            $pageTypesAll = $this->_getLayoutProcessor($layoutUpdateParams)->getPageHandlesHierarchy();
            foreach ($pageTypesAll as $pageTypeName => $pageTypeInfo) {
                $layoutMerge = $this->_getLayoutProcessor($layoutUpdateParams);
                $layoutMerge->addPageHandles(array($pageTypeName));
                $layoutMerge->load();
                if (!$layoutMerge->getContainers()) {
                    continue;
                }
                $pageTypes[$pageTypeName] = $pageTypeInfo;
            }
            $this->_addPageTypeOptions($pageTypes);
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
     * @return \Magento\View\Layout\ProcessorInterface
     */
    protected function _getLayoutProcessor(array $arguments)
    {
        return $this->_layoutProcessorFactory->create($arguments);
    }

    /**
     * Add page types information to the options
     *
     * @param array $pageTypes
     * @param int $level
     */
    protected function _addPageTypeOptions(array $pageTypes, $level = 0)
    {
        foreach ($pageTypes as $pageTypeName => $pageTypeInfo) {
            $params = array();
            if ($pageTypeInfo['type'] == \Magento\Core\Model\Layout\Merge::TYPE_FRAGMENT) {
                $params['class'] = 'fragment';
            }
            $this->addOption($pageTypeName, str_repeat('. ', $level) . $pageTypeInfo['label'], $params);
            $this->_addPageTypeOptions($pageTypeInfo['children'], $level + 1);
        }
    }
}
