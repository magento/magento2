<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Category form input image element
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\UrlInterface;

/**
 * Class \Magento\Framework\Data\Form\Element\Image
 *
 * @since 2.0.0
 */
class Image extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('file');
    }

    /**
     * Return element html code
     *
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $html = '';

        if ((string)$this->getValue()) {
            $url = $this->_getUrl();

            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $url;
            }

            $html = '<a href="' .
                $url .
                '"' .
                ' onclick="imagePreview(\'' .
                $this->getHtmlId() .
                '_image\'); return false;" ' .
                $this->_getUiId(
                    'link'
                ) .
                '>' .
                '<img src="' .
                $url .
                '" id="' .
                $this->getHtmlId() .
                '_image" title="' .
                $this->getValue() .
                '"' .
                ' alt="' .
                $this->getValue() .
                '" height="22" width="22" class="small-image-preview v-middle"  ' .
                $this->_getUiId() .
                ' />' .
                '</a> ';
        }
        $this->setClass('input-file');
        $html .= parent::getElementHtml();
        $html .= $this->_getDeleteCheckbox();

        return $html;
    }

    /**
     * Return html code of delete checkbox element
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($this->getValue()) {
            $label = (string)new \Magento\Framework\Phrase('Delete Image');
            $html .= '<span class="delete-image">';
            $html .= '<input type="checkbox"' .
                ' name="' .
                parent::getName() .
                '[delete]" value="1" class="checkbox"' .
                ' id="' .
                $this->getHtmlId() .
                '_delete"' .
                ($this->getDisabled() ? ' disabled="disabled"' : '') .
                '/>';
            $html .= '<label for="' .
                $this->getHtmlId() .
                '_delete"' .
                ($this->getDisabled() ? ' class="disabled"' : '') .
                '> ' .
                $label .
                '</label>';
            $html .= $this->_getHiddenInput();
            $html .= '</span>';
        }

        return $html;
    }

    /**
     * Return html code of hidden element
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="' . parent::getName() . '[value]" value="' . $this->getValue() . '" />';
    }

    /**
     * Get image preview url
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getUrl()
    {
        return $this->getValue();
    }

    /**
     * Return name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->getData('name');
    }
}
