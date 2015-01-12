<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block\System\Config\Form\Field;

/**
 * Class Export
 */
class Export extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];

        $url = $this->getUrl("*/PageCache/exportVarnishConfig", $params);
        $data = [
            'id' => 'system_full_page_cache_varnish_export_button',
            'label' => __('Export VCL'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }

    /**
     * Return PageCache TTL value from config
     * to avoid saving empty field
     *
     * @return string
     */
    public function getTtlValue()
    {
        return $this->_scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TTL);
    }
}
