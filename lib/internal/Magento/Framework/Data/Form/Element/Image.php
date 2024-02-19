<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Category form input image element
 *
 * @api
 */
class Image extends AbstractElement
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $random = $random ?? ObjectManager::getInstance()->get(Random::class);
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
        $this->setType('file');
        $this->secureRenderer = $secureRenderer;
        $this->random = $random;
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';

        if ((string)$this->getValue()) {
            $url = $this->_getUrl();

            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $url;
            }

            $linkId = 'linkId' .$this->random->getRandomString(8);
            $html = '<a previewlinkid="' .$linkId .'" href="' .
                $url .
                '" ' .
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
            $html .= $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                "imagePreview('{$this->getHtmlId()}_image');\nreturn false;",
                "*[previewlinkid='{$linkId}']"
            );
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
     */
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="' . parent::getName() . '[value]" value="' . $this->getValue() . '" />';
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        return $this->getValue();
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }
}
