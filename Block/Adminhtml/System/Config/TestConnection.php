<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block\Adminhtml\System\Config;

/**
 * Search engine test connection block
 *
 */
class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Magento_AdvancedSearch::system/config/testconnection.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('catalog/search_system_config_testconnection/ping'),
                'field_mapping' => [],
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Prepares field mapping array for use in block
     *
     * @param array $fieldMapping
     * @return string
     */
    public function prepareFieldMapping($fieldMapping = [])
    {
        return addslashes(json_encode($fieldMapping));
    }
}
