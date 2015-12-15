<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Helper\Data as DataHelper;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Editor as EditorElement;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * SimpleWysiwyg
 */
class SimpleWysiwyg extends AbstractElement
{
    const NAME = 'simplewysiwyg';

    /**
     * @var EditorElement
     */
    protected $editorElement;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg
     */
    protected $wysiwyg;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var DataHelper
     */
    protected $backendData;

    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;

    /**
     * @var CategoryAttributeRepositoryInterface
     */
    protected $categoryAttributeRepository;

    /**
     * @param ContextInterface $context
     * @param WysiwygConfig $wysiwygConfig
     * @param LayoutInterface $layout
     * @param DataHelper $backendData
     * @param CategoryAttributeRepositoryInterface $categoryAttributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        WysiwygConfig $wysiwygConfig,
        LayoutInterface $layout,
        DataHelper $backendData,
        CategoryAttributeRepositoryInterface $categoryAttributeRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_data = $data;
        $this->layout = $layout;
        $this->backendData = $backendData;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $data['config']['content'] = $this->getHtmlContent();
        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return bool
     */
    protected function isWysiwygEnabled()
    {
        return (bool)($this->wysiwygConfig->isEnabled() && $this->getEntityAttribute()->getIsWysiwygEnabled());
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface
     */
    protected function getEntityAttribute()
    {
        return $this->categoryAttributeRepository->get($this->getName());
    }

    /**
     * @return string
     */
    protected function getHtmlContent()
    {
        $htmlId = 'simple_wysiwyg';

        $html = "<textarea name=\"description\" id=\"$htmlId\"></textarea>";
        if ($this->isWysiwygEnabled()) {
            $config = json_encode($this->wysiwygConfig->getConfig()->getData());

            $html .= $this->layout->createBlock(
                Button::class,
                '',
                [
                    'data' => [
                        'label' => __('WYSIWYG Editor'),
                        'type' => 'button',
                        'class' => 'action-wysiwyg',
                        'onclick' => 'catalogWysiwygEditor.open(\'' . $this->backendData->getUrl(
                            'catalog/product/wysiwyg'
                        ) . '\', \'' . $htmlId . '\')',
                    ]
                ]
            )->toHtml();

            $html .= <<<HTML
<script>
require([
    'jquery',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function(jQuery){

var config = $config,
    editor;

jQuery.extend(config, {
    settings: {
        theme_advanced_buttons1 : 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
            'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
        theme_advanced_buttons2: null,
        theme_advanced_buttons3: null,
        theme_advanced_buttons4: null,
        theme_advanced_statusbar_location: null
    },
    files_browser_window_url: false
});

editor = new tinyMceWysiwygSetup(
    '$htmlId',
    config
);

editor.turnOn();

jQuery('#${htmlId}')
    .addClass('wysiwyg-editor')
    .data(
        'wysiwygEditor',
        editor
    );
});
</script>
HTML;
        }
        return $html;
    }
}
