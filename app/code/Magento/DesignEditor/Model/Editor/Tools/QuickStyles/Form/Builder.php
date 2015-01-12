<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form;

use Magento\Framework\Data\Form;

/**
 * VDE area model
 */
class Builder
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory
     */
    protected $_rendererFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory
     */
    protected $_elementsFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory $configFactory
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory $rendererFactory
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory $elementsFactory
     */
    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory $configFactory,
        \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory $rendererFactory,
        \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory $elementsFactory
    ) {
        $this->_formFactory = $formFactory;
        $this->_configFactory = $configFactory;
        $this->_rendererFactory = $rendererFactory;
        $this->_elementsFactory = $elementsFactory;
    }

    /**
     * Create varien data form with provided params
     *
     * @param array $data
     * @return Form
     * @throws \InvalidArgumentException
     */
    public function create(array $data = [])
    {
        $isFilePresent = true;
        try {
            $this->_config = $this->_configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_QUICK_STYLES,
                $data['theme'],
                $data['parent_theme']
            );
        } catch (\Magento\Framework\Exception $e) {
            $isFilePresent = false;
        }

        if (!isset($data['tab'])) {
            throw new \InvalidArgumentException(sprintf('Invalid controls tab "%s".', $data['tab']));
        }

        if ($isFilePresent) {
            /** @var $form Form */
            $form = $this->_formFactory->create(['data' => $data]);

            $this->_addElementTypes($form);

            $columns = $this->_initColumns($form, $data['tab']);
            $this->_populateColumns($columns, $data['tab']);
        } else {
            $form = $this->_formFactory->create(['data' => ['action' => '#']]);
        }

        if ($this->_isFormEmpty($form)) {
            $hintMessage = __('Sorry, but you cannot edit these theme styles.');
            $form->addField(
                $data['tab'] . '-tab-error',
                'note',
                ['after_element_html' => '<p class="error-notice">' . $hintMessage . '</p>'],
                '^'
            );
        }
        return $form;
    }

    /**
     * Check is any elements present in form
     *
     * @param Form $form
     * @return bool
     */
    protected function _isFormEmpty($form)
    {
        $isEmpty = true;
        /** @var  $elements \Magento\Framework\Data\Form\Element\Collection */
        $elements = $form->getElements();
        foreach ($elements as $element) {
            if ($element->getElements()->count()) {
                $isEmpty = false;
                break;
            }
        }
        return $isEmpty;
    }

    /**
     * Add column elements to form
     *
     * @param Form $form
     * @param string $tab
     * @return array
     */
    protected function _initColumns($form, $tab)
    {
        /** @var $columnLeft \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
        $columnLeft = $form->addField('column-left-' . $tab, 'column', []);
        $columnLeft->setRendererFactory($this->_rendererFactory)->setElementsFactory($this->_elementsFactory);

        /** @var $columnMiddle \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
        $columnMiddle = $form->addField('column-middle-' . $tab, 'column', []);
        $columnMiddle->setRendererFactory($this->_rendererFactory)->setElementsFactory($this->_elementsFactory);

        /** @var $columnRight \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
        $columnRight = $form->addField('column-right-' . $tab, 'column', []);
        $columnRight->setRendererFactory($this->_rendererFactory)->setElementsFactory($this->_elementsFactory);

        $columns = ['left' => $columnLeft, 'middle' => $columnMiddle, 'right' => $columnRight];

        return $columns;
    }

    /**
     * Populate columns with fields
     *
     * @param array $columns
     * @param string $tab
     * @return void
     */
    protected function _populateColumns($columns, $tab)
    {
        foreach ($this->_config->getAllControlsData() as $id => $control) {
            $positionData = $control['layoutParams'];
            unset($control['layoutParams']);

            if ($positionData['tab'] != $tab) {
                continue;
            }

            $config = $this->_buildElementConfig($id, $positionData, $control);

            /** @var $column \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column */
            $column = $columns[$positionData['column']];
            $column->addField($id, $control['type'], $config);
        }
    }

    /**
     * Create form element config
     *
     * @param string $htmlId
     * @param array $positionData
     * @param array $control
     * @return array
     */
    protected function _buildElementConfig($htmlId, $positionData, $control)
    {
        $label = __($positionData['title']);

        $config = ['name' => $htmlId, 'label' => $label];
        if (isset($control['components'])) {
            $config['components'] = $control['components'];
            $config['title'] = $label;
        } else {
            $config['value'] = $control['value'];
            $config['title'] = htmlspecialchars(
                sprintf('%s {%s: %s}', $control['selector'], $control['attribute'], $control['value']),
                ENT_COMPAT
            );
            if (isset($control['options'])) {
                $config['options'] = $control['options'];
            }
        }

        return $config;
    }

    /**
     * Add custom element types
     *
     * @param Form $form
     * @return void
     */
    protected function _addElementTypes($form)
    {
        $form->addType('column', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column');
    }
}
