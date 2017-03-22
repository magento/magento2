<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;

/**
 * Class Input
 */
class Wysiwyg extends AbstractElement
{
    const NAME = 'wysiwyg';

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Editor
     */
    protected $editor;

    /**
     * @param ContextInterface $context
     * @param FormFactory $formFactory
     * @param ConfigInterface $wysiwygConfig
     * @param array $components
     * @param array $data
     * @param array $config
     */
    public function __construct(
        ContextInterface $context,
        FormFactory $formFactory,
        ConfigInterface $wysiwygConfig,
        array $components = [],
        array $data = [],
        array $config = []
    ) {
        $wysiwygConfigData = isset($config['wysiwygConfigData']) ? $config['wysiwygConfigData'] : [];
        $this->form = $formFactory->create();
        $this->editor = $this->form->addField(
            $context->getNamespace() . '_' . $data['name'],
            \Magento\Framework\Data\Form\Element\Editor::class,
            [
                'force_load' => true,
                'rows' => 20,
                'name' => $data['name'],
                'config' => $wysiwygConfig->getConfig($wysiwygConfigData),
                'wysiwyg' => isset($config['wysiwyg']) ? $config['wysiwyg'] : null,
            ]
        );
        $data['config']['content'] = $this->editor->getElementHtml();

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
