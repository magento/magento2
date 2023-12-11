<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;

/**
 * WYSIWYG form element
 *
 * @api
 * @since 100.1.0
 */
class Wysiwyg extends AbstractElement
{
    const NAME = 'wysiwyg';

    /**
     * @var Form
     * @since 100.1.0
     */
    protected $form;

    /**
     * @var Editor
     * @since 100.1.0
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
        $wysiwygId = $context->getNamespace() . '_' . $data['name'];
        $this->editor = $this->form->addField(
            $wysiwygId,
            \Magento\Framework\Data\Form\Element\Editor::class,
            [
                'force_load' => true,
                'rows' => isset($config['rows']) ? $config['rows'] : 20,
                'name' => $data['name'],
                'config' => $wysiwygConfig->getConfig($wysiwygConfigData),
                'wysiwyg' => isset($config['wysiwyg']) ? $config['wysiwyg'] : null,
            ]
        );
        $data['config']['content'] = $this->editor->getElementHtml();
        $data['config']['wysiwygId'] = $wysiwygId;

        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
