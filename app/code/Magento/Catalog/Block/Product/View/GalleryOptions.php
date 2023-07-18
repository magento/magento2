<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Gallery options block.
 */
class GalleryOptions extends AbstractView implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Gallery
     */
    private $gallery;

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param Json $jsonSerializer
     * @param Gallery $gallery
     * @param array $data
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        Json $jsonSerializer,
        Gallery $gallery,
        array $data = []
    ) {
        $this->gallery = $gallery;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * Retrieve gallery options in JSON format
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getOptionsJson()
    {
        $optionItems = null;

        //Special case for gallery/nav which can be the string "thumbs/false/dots"
        if (is_bool($this->getVar("gallery/nav"))) {
            $optionItems['nav'] = $this->getVar("gallery/nav") ? 'true' : 'false';
        } else {
            $optionItems['nav'] = $this->escapeHtml($this->getVar("gallery/nav"));
        }

        $optionItems['loop'] = $this->getVar("gallery/loop");
        $optionItems['keyboard'] = $this->getVar("gallery/keyboard");
        $optionItems['arrows'] = $this->getVar("gallery/arrows");
        $optionItems['allowfullscreen'] = $this->getVar("gallery/allowfullscreen");
        $optionItems['showCaption'] = $this->getVar("gallery/caption");
        $optionItems['width'] = (int)$this->escapeHtml(
            $this->gallery->getImageAttribute('product_page_image_medium', 'width')
        );
        $optionItems['thumbwidth'] = (int)$this->escapeHtml(
            $this->gallery->getImageAttribute('product_page_image_small', 'width')
        );

        if ($this->gallery->getImageAttribute('product_page_image_small', 'height') ||
            $this->gallery->getImageAttribute('product_page_image_small', 'width')) {
            $optionItems['thumbheight'] = (int)$this->escapeHtml(
                $this->gallery->getImageAttribute('product_page_image_small', 'height') ?:
                    $this->gallery->getImageAttribute('product_page_image_small', 'width')
            );
        }

        if ($this->gallery->getImageAttribute('product_page_image_medium', 'height') ||
            $this->gallery->getImageAttribute('product_page_image_medium', 'width')) {
            $optionItems['height'] = (int)$this->escapeHtml(
                $this->gallery->getImageAttribute('product_page_image_medium', 'height') ?:
                    $this->gallery->getImageAttribute('product_page_image_medium', 'width')
            );
        }

        if ($this->getVar("gallery/transition/duration")) {
            $optionItems['transitionduration'] =
                (int)$this->escapeHtml($this->getVar("gallery/transition/duration"));
        }

        $optionItems['transition'] = $this->escapeHtml($this->getVar("gallery/transition/effect"));
        $optionItems['navarrows'] = $this->getVar("gallery/navarrows");
        $optionItems['navtype'] = $this->escapeHtml($this->getVar("gallery/navtype"));
        $optionItems['navdir'] = $this->escapeHtml($this->getVar("gallery/navdir"));

        if ($this->getVar("gallery/thumbmargin")) {
            $optionItems['thumbmargin'] = (int)$this->escapeHtml($this->getVar("gallery/thumbmargin"));
        }

        if ($this->getVar("product_image_white_borders")) {
            $optionItems['whiteBorders'] =
                (int)$this->escapeHtml($this->getVar("product_image_white_borders"));
        }

        return $this->jsonSerializer->serialize($optionItems);
    }

    /**
     * Retrieve gallery fullscreen options in JSON format
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getFSOptionsJson()
    {
        $fsOptionItems = null;

        //Special case for gallery/nav which can be the string "thumbs/false/dots"
        if (is_bool($this->getVar("gallery/fullscreen/nav"))) {
            $fsOptionItems['nav'] = $this->getVar("gallery/fullscreen/nav") ? 'true' : 'false';
        } else {
            $fsOptionItems['nav'] = $this->escapeHtml($this->getVar("gallery/fullscreen/nav"));
        }

        $fsOptionItems['loop'] = $this->getVar("gallery/fullscreen/loop");
        $fsOptionItems['navdir'] = $this->escapeHtml($this->getVar("gallery/fullscreen/navdir"));
        $fsOptionItems['navarrows'] = $this->getVar("gallery/fullscreen/navarrows");
        $fsOptionItems['navtype'] = $this->escapeHtml($this->getVar("gallery/fullscreen/navtype"));
        $fsOptionItems['arrows'] = $this->getVar("gallery/fullscreen/arrows");
        $fsOptionItems['showCaption'] = $this->getVar("gallery/fullscreen/caption");

        if ($this->getVar("gallery/fullscreen/transition/duration")) {
            $fsOptionItems['transitionduration'] = (int)$this->escapeHtml(
                $this->getVar("gallery/fullscreen/transition/duration")
            );
        }

        $fsOptionItems['transition'] = $this->escapeHtml($this->getVar("gallery/fullscreen/transition/effect"));

        if ($this->getVar("gallery/fullscreen/keyboard")) {
            $fsOptionItems['keyboard'] = $this->getVar("gallery/fullscreen/keyboard");
        }

        if ($this->getVar("gallery/fullscreen/thumbmargin")) {
            $fsOptionItems['thumbmargin'] =
                (int)$this->escapeHtml($this->getVar("gallery/fullscreen/thumbmargin"));
        }

        if ($this->getVar("product_image_white_borders")) {
            $fsOptionItems['whiteBorders'] =
                (int)$this->escapeHtml($this->getVar("product_image_white_borders"));
        }

        return $this->jsonSerializer->serialize($fsOptionItems);
    }
}
