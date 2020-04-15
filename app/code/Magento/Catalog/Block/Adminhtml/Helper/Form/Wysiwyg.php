<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog textarea attribute WYSIWYG button
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Helper\Form;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Wysiwyg helper.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Wysiwyg extends \Magento\Framework\Data\Form\Element\Textarea
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager = null;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Helper\Data $backendData,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_layout = $layout;
        $this->_moduleManager = $moduleManager;
        $this->_backendData = $backendData;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $random = $random ?? ObjectManager::getInstance()->get(Random::class);
        $this->secureRenderer = $secureRenderer;
        $this->random = $random;
    }

    /**
     * Retrieve additional html and put it at the end of element html
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());

        $html = parent::getAfterElementHtml();
        if ($this->getIsWysiwygEnabled()) {
            $buttonId = 'wysiwyg_action_button_' . $this->random->getRandomString(32);
            $disabled = $this->getDisabled() || $this->getReadonly();
            $html .= $this->_layout->createBlock(
                \Magento\Backend\Block\Widget\Button::class,
                '',
                [
                    'data' => [
                        'id' => $buttonId,
                        'label' => __('WYSIWYG Editor'),
                        'type' => 'button',
                        'disabled' => $disabled,
                        'class' => 'action-wysiwyg',
                    ]
                ]
            )->toHtml();
            $scriptString = <<<HTML
require([
    'jquery',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function(jQuery){

var config = $config,
    editor;

editor = new wysiwygSetup(
    '{$this->getHtmlId()}',
    config
);

editor.turnOn();

jQuery('#{$this->getHtmlId()}')
    .addClass('wysiwyg-editor')
    .data(
        'wysiwygEditor',
        editor
    );
});
HTML;
            $html .= /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);
            $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                'catalogWysiwygEditor.open(\'' . $this->_backendData->getUrl(
                    'catalog/product/wysiwyg'
                ) . '\', \'' . $this->getHtmlId() . '\')',
                $buttonId
            );
        }

        return $html;
    }

    /**
     * Check whether wysiwyg enabled or not
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsWysiwygEnabled()
    {
        if ($this->_moduleManager->isEnabled('Magento_Cms')) {
            return (bool)($this->_wysiwygConfig->isEnabled() && $this->getEntityAttribute()->getIsWysiwygEnabled());
        }

        return false;
    }
}
