<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Renderer for URL key input
 * Allows to manage and overwrite URL Rewrites History save settings
 */
class UrlKeyRenderer extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    const XML_PATH_SEO_SAVE_HISTORY = 'catalog/seo/save_rewrites_history';

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement $element */
        $element = $this->getElement();
        if (!$element->getValue()) {
            return parent::getElementHtml();
        }
        $element->setOnkeyup("onUrlkeyChanged('" . $element->getHtmlId() . "')");
        $element->setOnchange("onUrlkeyChanged('" . $element->getHtmlId() . "')");

        $data = ['name' => $element->getData('name') . '_create_redirect', 'disabled' => true];
        /** @var \Magento\Framework\Data\Form\Element\Hidden $hidden */
        $hidden = $this->_elementFactory->create('hidden', ['data' => $data]);
        $hidden->setForm($element->getForm());

        $storeId = $element->getForm()->getDataObject()->getStoreId();
        $data['html_id'] = $element->getHtmlId() . '_create_redirect';
        $data['label'] = __('Create Permanent Redirect for old URL');
        $data['value'] = $element->getValue();
        $data['checked'] = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_SEO_SAVE_HISTORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        /** @var \Magento\Framework\Data\Form\Element\Checkbox $checkbox */
        $checkbox = $this->_elementFactory->create('checkbox', ['data' => $data]);
        $checkbox->setForm($element->getForm());

        return parent::getElementHtml() . '<br/>' . $hidden->getElementHtml() . $checkbox->getElementHtml()
            . $checkbox->getLabelHtml();
    }
}
