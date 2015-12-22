<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Ui\Component\Wysiwyg\ConfigInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Editor as EditorElement;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Input
 */
class Wysiwyg extends AbstractElement
{
    const NAME = 'wysiwyg';

    /**
     * @var EditorElement
     */
    protected $editorElement;

    /**
     * Wysiwyg constructor.
     * @param ContextInterface $context
     * @param Form $form
     * @param EditorElement $editorElement
     * @param ConfigInterface $wysiwygConfig
     * @param array $components
     * @param array $data
     * @param array $config
     */
    public function __construct(
        ContextInterface $context,
        Form $form,
        EditorElement $editorElement,
        ConfigInterface $wysiwygConfig,
        array $components = [],
        array $data = [],
        array $config = []
    ) {
        $this->editorElement = $editorElement;
        $this->editorElement->setForm($form);
        $this->editorElement->setData('force_load', true);
        $this->editorElement->setData('rows', 20);
        $this->editorElement->setData('name', $data['name']);
        $this->editorElement->setData('html_id', $data['name'] . 'Editor');

        $wysiwygConfigData = isset($config['wysiwygConfigData']) ? $config['wysiwygConfigData'] : [];
        $this->editorElement->setConfig($wysiwygConfig->getConfig($wysiwygConfigData));

        $data['config']['content'] = $editorElement->getElementHtml();
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
}
