<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Composite;

/**
 * Parent composite form element for VDE
 *
 * This elements know about renderer factory and use it to set renders to its children
 *
 * @method array getComponents()
 * @method string getFieldsetContainerId()
 * @method bool getCollapsable()
 * @method string getHeaderBar()
 * @method string getLegend()
 * @method string getFieldsetType()
 * @method string getAdvancedPosition()
 * @method string getNoContainer()
 * @method string getComment()
 * @method string getClass()
 * @method bool hasHtmlContent()
 * @method string getHtmlContent()
 * @method string getLabel()
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Composite\AbstractComposite setLegend($legend)
 */
abstract class AbstractComposite extends \Magento\Framework\Data\Form\Element\Fieldset implements
    \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ContainerInterface
{
    /**
     * Delimiter for name parts in composite controls
     */
    const CONTROL_NAME_DELIMITER = ':';

    /**
     * Factory that creates renderer for element by element class
     *
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory
     */
    protected $_rendererFactory;

    /**
     * Factory that creates element by element type
     *
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory
     */
    protected $_elementsFactory;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory $elementsFactory
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory $rendererFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory $elementsFactory,
        \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory $rendererFactory,
        $data = []
    ) {
        $this->_elementsFactory = $elementsFactory;
        $this->_rendererFactory = $rendererFactory;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Constructor helper
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setLegend($this->getLabel());

        $this->_addElementTypes();
        $this->_addFields();

        $this->addClass('element-' . static::CONTROL_TYPE);
    }

    /**
     * Add fields to composite composite element
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param boolean $after
     * @param boolean $isAdvanced
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function addField($elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        if (isset($this->_types[$type])) {
            $className = $this->_types[$type];
        } else {
            $className = 'Magento\\Framework\\Data\\Form\\Element\\' . ucfirst(strtolower($type));
        }
        $element = $this->_elementsFactory->create($className, $config);
        $element->setId($elementId);
        $this->addElement($element, $after);

        $layoutName = $element->getId() . '-renderer';
        try {
            $renderer = $this->_rendererFactory->create($className, $layoutName);
        } catch (\Magento\Framework\Model\Exception $e) {
            $renderer = null;
        }
        if ($renderer) {
            $element->setRenderer($renderer);
        }
        $element->setAdvanced($isAdvanced);
        return $element;
    }

    /**
     * Get controls component of given type
     *
     * @param string $type
     * @param string|null $subtype
     * @return array
     * @throws \Magento\Framework\Model\Exception
     */
    public function getComponent($type, $subtype = null)
    {
        $components = $this->getComponents();
        $componentId = $this->getComponentId($type);
        if (!isset($components[$componentId])) {
            throw new \Magento\Framework\Model\Exception(
                __('Component of the type "%1" is not found between elements of "%2"', $type, $this->getData('name'))
            );
        }
        $component = $components[$componentId];

        if ($subtype) {
            $subComponentId = $this->getComponentId($subtype);
            $component = $component['components'][$subComponentId];
        }

        return $component;
    }

    /**
     * Get id that component of given type should have
     *
     * @param string $type
     * @return string
     */
    public function getComponentId($type)
    {
        $names = explode(self::CONTROL_NAME_DELIMITER, $this->getData('name'));
        return join('', [array_shift($names), self::CONTROL_NAME_DELIMITER, $type]);
    }

    /**
     * Add form elements
     *
     * @return $this
     */
    abstract protected function _addFields();

    /**
     * Add element types used in composite font element
     *
     * @return $this
     */
    abstract protected function _addElementTypes();
}
