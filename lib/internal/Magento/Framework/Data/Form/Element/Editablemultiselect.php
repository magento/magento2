<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form editable select element
 *
 * Element allows inline modification of textual data within select
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Form editable multiselect element.
 */
class Editablemultiselect extends \Magento\Framework\Data\Form\Element\Multiselect
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

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
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $random = $random ?? ObjectManager::getInstance()->get(Random::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->secureRenderer = $secureRenderer;
        $this->random = $random;
    }

    /**
     * Name of the default JavaScript class that is used to make multiselect editable
     *
     * This class must define init() method and receive configuration in the constructor
     */
    public const DEFAULT_ELEMENT_JS_CLASS = 'EditableMultiselect';

    /**
     * Retrieve HTML markup of the element
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();

        $selectConfig = $this->getData('select_config');
        if ($this->getData('disabled')) {
            $selectConfig['is_entity_editable'] = false;
        }

        $elementJsClass = self::DEFAULT_ELEMENT_JS_CLASS;
        if ($this->getData('element_js_class')) {
            $elementJsClass = $this->getData('element_js_class');
        }

        $selectConfigJson = $this->serializer->serialize($selectConfig);
        $jsObjectName = $this->getJsObjectName();

        // TODO: TaxRateEditableMultiselect should be moved to a static .js module.
        $html .= $this->secureRenderer->renderTag(
            'script',
            ['type' => 'text/javascript'],
            <<<script
                require([
                    'jquery'
                ], function( $ ){

                    function isResolved(){
                        return typeof window['{$elementJsClass}'] !== 'undefined';
                    }

                    function init(){
                        var {$jsObjectName} = new {$elementJsClass}({$selectConfigJson});

                        {$jsObjectName}.init();
                    }

                    function check( tries, delay ){
                        if( isResolved() ){
                            init();
                        }
                        else if( tries-- ){
                            setTimeout( check.bind(this, tries, delay), delay);
                        }
                        else{
                            console.warn( 'Unable to resolve dependency: {$elementJsClass}' );
                        }
                    }

                   check(8, 500);

                });
script
            ,
            false
        );

        return $html;
    }

    /**
     * Retrieve HTML markup of given select option
     *
     * @param array $option
     * @param string[] $selected
     *
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $optionId = 'optId' . $this->random->getRandomString(8);
        $html = '<option value="' . $this->_escape($option['value']) . '" id="' . $optionId . '" ';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        if (in_array((string)$option['value'], $selected)) {
            $html .= ' selected="selected"';
        }

        if ($this->getData('disabled')) {
            // if element is disabled then no data modification is allowed
            $html .= ' disabled="disabled" data-is-removable="no" data-is-editable="no"';
        }

        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        if (!empty($option['style'])) {
            $html .= $this->secureRenderer->renderStyleAsTag($option['style'], "#$optionId");
        }

        return $html;
    }
}
