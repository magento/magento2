<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product form image field helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Image extends \Magento\Framework\Data\Form\Element\Image
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Return generated url.
     *
     * @return bool|string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->_urlBuilder->getBaseUrl(
                ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
            ) . 'catalog/product/' . $this->getValue();
        }

        return $url;
    }

    /**
     * Return generated delete checkbox.
     *
     * @return string
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($attribute = $this->getEntityAttribute()) {
            if (!$attribute->getIsRequired()) {
                $html .= parent::_getDeleteCheckbox();
            } else {
                $inputField = '<input value="%s" id="%s_hidden" type="hidden" class="required-entry" />';
                $html .= sprintf($inputField, $this->getValue(), $this->getHtmlId());
                $scriptString = 'require(["prototype"], function(){
                    syncOnchangeValue(\'' .
                    $this->getHtmlId() .
                    '\', \'' .
                    $this->getHtmlId() .
                    '_hidden\');
                });';
                $html .= /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);
            }
        } else {
            $html .= parent::_getDeleteCheckbox();
        }

        return $html;
    }
}
