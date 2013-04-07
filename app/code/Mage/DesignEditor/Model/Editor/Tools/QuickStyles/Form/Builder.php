<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * VDE area model
 */
class Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Builder
{
    /**
     * @var Varien_Data_Form_Factory
     */
    protected $_formFactory;

    /**
     * @var Mage_Core_Model_Translate
     */
    protected $_translator;

    /**
     * @var Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Renderer_Factory
     */
    protected $_rendererFactory;

    /**
     * @var Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Element_Factory
     */
    protected $_elementsFactory;

    /** @var Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
    protected $_configFactory;

    /** @var Mage_DesignEditor_Model_Config_Control_QuickStyles */
    protected $_config;

    /**
     * Constructor
     *
     * @param Varien_Data_Form_Factory $formFactory
     * @param Mage_DesignEditor_Model_Editor_Tools_Controls_Factory $configFactory
     * @param Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Renderer_Factory $rendererFactory
     * @param Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Element_Factory $elementsFactory
     * @param Mage_Core_Model_Translate $translator
     */
    public function __construct(
        Varien_Data_Form_Factory $formFactory,
        Mage_DesignEditor_Model_Editor_Tools_Controls_Factory $configFactory,
        Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Renderer_Factory $rendererFactory,
        Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Form_Element_Factory $elementsFactory,
        Mage_Core_Model_Translate $translator
    ) {
        $this->_formFactory     = $formFactory;
        $this->_configFactory   = $configFactory;
        $this->_rendererFactory = $rendererFactory;
        $this->_elementsFactory = $elementsFactory;
        $this->_translator      = $translator;
    }

    /**
     * Create varien data form with provided params
     *
     * @param array $data
     * @return Varien_Data_Form
     * @throws InvalidArgumentException
     */
    public function create(array $data = array())
    {
        $this->_config = $this->_configFactory->create(
            Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES,
            $data['theme']
        );

        /** @var $form Varien_Data_Form */
        $form = $this->_formFactory->create($data);

        $this->_addElementTypes($form);

        if (!isset($data['tab'])) {
            throw new InvalidArgumentException((sprintf('Invalid controls tab "%s".', $data['tab'])));
        }

        $columns = $this->_initColumns($form, $data['tab']);
        $this->_populateColumns($columns, $data['tab']);

        return $form;
    }

    /**
     * Add column elements to form
     *
     * @param Varien_Data_Form $form
     * @param string $tab
     * @return array
     */
    protected function _initColumns($form, $tab)
    {
        /** @var $columnLeft Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Column */
        $columnLeft = $form->addField('column-left-' . $tab, 'column', array());
        $columnLeft->setRendererFactory($this->_rendererFactory)
            ->setElementsFactory($this->_elementsFactory);

        /** @var $columnMiddle Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Column */
        $columnMiddle = $form->addField('column-middle-' . $tab, 'column', array());
        $columnMiddle->setRendererFactory($this->_rendererFactory)
            ->setElementsFactory($this->_elementsFactory);

        /** @var $columnRight Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Column */
        $columnRight = $form->addField('column-right-' . $tab, 'column', array());
        $columnRight->setRendererFactory($this->_rendererFactory)
            ->setElementsFactory($this->_elementsFactory);

        $columns = array(
            'left'   => $columnLeft,
            'middle' => $columnMiddle,
            'right'  => $columnRight
        );

        return $columns;
    }

    /**
     * Populate columns with fields
     *
     * @param array $columns
     * @param string $tab
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

            /** @var $column Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Column */
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
        $label = $this->__($positionData['title']);

        $config = array(
            'name'  => $htmlId,
            'label' => $label,
        );
        if (isset($control['components'])) {
            $config['components'] = $control['components'];
            $config['title'] = $label;
        } else {
            $config['value'] = $control['value'];
            $config['title'] = sprintf('%s {%s: %s}',
                $control['selector'],
                $control['attribute'],
                $control['value']
            );
            if (isset($control['options'])) {
                $config['options'] =  $control['options'];
            }
        }

        return $config;
    }

    /**
     * Add custom element types
     *
     * @param Varien_Data_Form $form
     */
    protected function _addElementTypes($form)
    {
        $form->addType('column', 'Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Column');
    }

    /**
     * Translate sentence
     *
     * @return string
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    protected function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), 'Mage_DesignEditor');
        array_unshift($args, $expr);
        return $this->_translator->translate($args);
    }
}
