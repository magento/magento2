<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Widget Instance Properties tab block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

/**
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Properties extends \Magento\Widget\Block\Adminhtml\Widget\Options implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Widget config parameters
     *
     * @var array
     */
    protected $hiddenParameters = [
        'template'
    ];

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Widget Options');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Widget Options');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return $this->getWidgetInstance()->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        $widgetConfig = $this->getWidgetInstance()->getWidgetConfigAsArray();

        if (isset($widgetConfig['parameters'])) {
            foreach ($widgetConfig['parameters'] as $key => $parameter) {
                if ($parameter['visible'] == 1 && !in_array($key, $this->hiddenParameters)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Getter
     *
     * @return \Magento\Widget\Model\Widget\Instance
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('current_widget_instance');
    }

    /**
     * Prepare block children and data.
     * Set widget type and widget parameters if available
     *
     * @return $this
     */
    protected function _preparelayout()
    {
        $this->setWidgetType(
            $this->getWidgetInstance()->getType()
        )->setWidgetValues(
            $this->getWidgetInstance()->getWidgetParameters()
        );
        return parent::_prepareLayout();
    }

    /**
     * Add field to Options form based on option configuration
     *
     * @param \Magento\Framework\DataObject $parameter
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function _addField($parameter)
    {
        if (!in_array($parameter->getKey(), $this->hiddenParameters)) {
            return parent::_addField($parameter);
        }
        return false;
    }
}
