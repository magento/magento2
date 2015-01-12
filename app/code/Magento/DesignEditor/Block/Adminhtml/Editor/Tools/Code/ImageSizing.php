<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code;

use Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration;
use Magento\Framework\Data\Form;

/**
 * Block that renders Custom tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class ImageSizing extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory
     */
    protected $_controlFactory;

    /**
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_themeContext;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory $controlFactory
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory $controlFactory,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_eavConfig = $eavConfig;
        $this->_controlFactory = $controlFactory;
        $this->_themeContext = $themeContext;
    }

    /**
     * Returns url to save action of image sizing
     *
     * @return string
     */
    public function getImageSizingUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/saveImageSizing',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var Form $form */
        $form = $this->_formFactory->create(['data' => ['action' => '#', 'method' => 'post']]);
        $form->setId('product_image_sizing_form');
        $this->setForm($form);
        $form->setUseContainer(true);
        $form->setFieldNameSuffix('imagesizing');
        $form->addType('button_button', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Button');

        $isFilePresent = true;
        try {
            /** @var $controlsConfig Configuration */
            $controlsConfig = $this->_controlFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_IMAGE_SIZING,
                $this->_themeContext->getStagingTheme()
            );
        } catch (\Magento\Framework\Exception $e) {
            $isFilePresent = false;
        }

        if ($isFilePresent) {
            $this->_initFormElements($controlsConfig, $form);
        } else {
            $hintMessage = __('Sorry, but you cannot resize images for this theme.');
            $form->addField(
                'imagesize-tab-error',
                'note',
                ['after_element_html' => '<p class="error-notice">' . $hintMessage . '</p>']
            );
        }

        return parent::_prepareForm();
    }

    /**
     * Initialize form elements
     *
     * @param Configuration $controlsConfig
     * @param Form $form
     * @return $this
     */
    protected function _initFormElements($controlsConfig, $form)
    {
        $hintMessage = __(
            'Please enter values for height and width.' .
            ' Use the chain icon if you want height and width to match.' .
            ' Be sure to see how it looks in your store.' .
            ' You may need to update your custom CSS file.'
        );

        $form->addField(
            'information_hint',
            'note',
            ['after_element_html' => '<p class="note">' . $hintMessage . '</p>']
        );

        $whiteBorder = $controlsConfig->getControlData('product_image_border');
        if ($whiteBorder) {
            $this->_addWhiteBorderElement($whiteBorder);
        }

        $controls = $controlsConfig->getAllControlsData();
        foreach ($controls as $name => $control) {
            if ($control['type'] != 'image-sizing') {
                continue;
            }
            $this->_addImageSizeFieldset($name, $control);
        }

        $fieldset = $form->addFieldset(
            'save_image_sizing_fieldset',
            ['name' => 'save_image_sizing_fieldset', 'fieldset_type' => 'field', 'class' => 'save_image_sizing']
        );
        $this->_addElementTypes($fieldset);

        if ($whiteBorder || $controls) {
            $fieldset->addField(
                'save_image_sizing',
                'button_button',
                [
                    'name' => 'save_image_sizing',
                    'title' => __('Update'),
                    'value' => __('Update'),
                    'data-mage-init' => $this->escapeHtml(
                        json_encode(['button' => ['event' => 'saveForm', 'target' => 'body']])
                    )
                ]
            );
        }
        return $this;
    }

    /**
     * Add white border checkbox to form
     *
     * @param array $control
     * @return $this
     */
    protected function _addWhiteBorderElement($control)
    {
        /** @var $form Form */
        $form = $this->getForm();
        $fieldMessage = __('Add white borders to images that are smaller than the container.');
        foreach ($control['components'] as $name => $component) {
            $form->addField('add_white_borders_hidden', 'hidden', ['name' => $name, 'value' => '0']);
            $form->addField(
                'add_white_borders',
                'checkbox',
                [
                    'name' => $name,
                    'checked' => !empty($component['value']),
                    'value' => '1',
                    'after_element_html' => $fieldMessage
                ]
            );
        }
        /** Get valid message from PO */
        $hintMessage = __(
            'If an image is too big,' .
            '  we automatically make it smaller and add white borders to fill the container.'
        );
        $form->addField(
            'add_white_borders_hint',
            'note',
            ['after_element_html' => '<p class="description">' . $hintMessage . '</p>']
        );

        return $this;
    }

    /**
     * Add one image sizing item to form
     *
     * @param string $name
     * @param array $control
     * @return $this
     */
    protected function _addImageSizeFieldset($name, $control)
    {
        /** @var $form Form */
        $form = $this->getForm();
        $fieldset = $form->addFieldset(
            $name,
            ['name' => $name, 'fieldset_type' => 'field', 'legend' => $control['layoutParams']['title']]
        );
        $this->_addElementTypes($fieldset);

        $defaultValues = [];
        foreach ($control['components'] as $componentName => $component) {
            $defaultValues[$componentName] = $component['default'];
            $this->_addFormElement($fieldset, $component, $componentName);
        }
        $this->_addResetButton($fieldset, $defaultValues, $name);

        return $this;
    }

    /**
     * Add image size form element by component type
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $component
     * @param string $componentName
     * @return $this
     */
    protected function _addFormElement($fieldset, $component, $componentName)
    {
        switch ($component['type']) {
            case 'image-type':
                $this->_addImageTypeElement($fieldset, $component, $componentName);
                break;
            case 'image-width':
                $this->_addImageWidthElement($fieldset, $component, $componentName);
                break;
            case 'image-ratio':
                $this->_addImageRatioElement($fieldset, $component, $componentName);
                break;
            case 'image-height':
                $this->_addImageHeightElement($fieldset, $component, $componentName);
                break;
        }
        return $this;
    }

    /**
     * Add image type form element to fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $component
     * @param string $componentName
     * @return $this
     */
    protected function _addImageTypeElement($fieldset, $component, $componentName)
    {
        $fieldset->addField(
            $componentName,
            'select',
            [
                'name' => $componentName,
                'values' => $this->_getSelectOptions(),
                'value' => $this->_getValue($component)
            ]
        );
        return $this;
    }

    /**
     * Add image width form element to fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $component
     * @param string $componentName
     * @return $this
     */
    protected function _addImageWidthElement($fieldset, $component, $componentName)
    {
        $fieldset->addField(
            $componentName,
            'text',
            [
                'name' => $componentName,
                'class' => 'image-width',
                'value' => $this->_getValue($component),
                'before_element_html' => '<span>W</span>'
            ]
        );
        return $this;
    }

    /**
     * Add image height form element to fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $component
     * @param string $componentName
     * @return $this
     */
    protected function _addImageHeightElement($fieldset, $component, $componentName)
    {
        $fieldset->addField(
            $componentName,
            'text',
            [
                'name' => $componentName,
                'class' => 'image-height',
                'value' => $this->_getValue($component),
                'before_element_html' => '<span>H</span>'
            ]
        );
        return $this;
    }

    /**
     * Add image ratio form element to fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $component
     * @param string $componentName
     * @return $this
     */
    protected function _addImageRatioElement($fieldset, $component, $componentName)
    {
        $fieldset->addField($componentName . '-hidden', 'hidden', ['name' => $componentName, 'value' => '0']);
        $fieldset->addField(
            $componentName,
            'checkbox',
            [
                'checked' => $this->_getValue($component) ? 'checked' : false,
                'name' => $componentName,
                'class' => 'image-ratio',
                'value' => '1',
                'after_element_html' => '<span class="action-connect"></span>'
            ]
        );
        return $this;
    }

    /**
     * Add reset button to fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $defaultValues
     * @param string $name
     * @return $this
     */
    protected function _addResetButton($fieldset, $defaultValues, $name)
    {
        $fieldset->addField(
            $name . '_reset',
            'button_button',
            [
                'name' => $name . '_reset',
                'title' => __('Reset to Original'),
                'value' => __('Reset to Original'),
                'class' => 'action-reset',
                'data-mage-init' => $this->escapeHtml(
                    json_encode(
                        [
                            'button' => [
                                'event' => 'restoreDefaultData',
                                'target' => 'body',
                                'eventData' => $defaultValues,
                            ],
                        ]
                    )
                )
            ]
        );
        return $this;
    }

    /**
     * Get value
     *
     * @param array $component
     * @return array
     */
    protected function _getValue($component)
    {
        return $component['value'] !== false ? $component['value'] : $component['default'];
    }

    /**
     * Return values for select element
     *
     * @return array
     */
    protected function _getSelectOptions()
    {
        $options = [];
        foreach ($this->getImageTypes() as $imageType) {
            $attribute = $this->_eavConfig->getAttribute('catalog_product', $imageType);
            $options[] = ['value' => $imageType, 'label' => $attribute->getFrontendLabel()];
        }
        return $options;
    }

    /**
     * Return product image types
     *
     * @return string[]
     */
    public function getImageTypes()
    {
        return ['image', 'small_image', 'thumbnail'];
    }

    /**
     * Set additional form button
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['button_button' => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Button'];
    }
}
