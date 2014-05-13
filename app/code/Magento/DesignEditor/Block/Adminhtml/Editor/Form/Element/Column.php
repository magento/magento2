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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

/**
 * Column renderer to Quick Styles panel in VDE
 *
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column setClass($class)
 */
class Column extends \Magento\Framework\Data\Form\Element\Fieldset implements
    \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ContainerInterface
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'column';

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory
     */
    protected $_rendererFactory;

    /**
     * @var \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory
     */
    protected $_elementsFactory;

    /**
     * Constructor helper
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_addElementTypes();
        $this->addClass(self::CONTROL_TYPE);
    }

    /**
     * Add element types that can be added to 'column' element
     *
     * @return $this
     */
    protected function _addElementTypes()
    {
        //contains composite font element and logo uploader
        $this->addType('logo', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Logo');

        //contains font picker, color picker
        $this->addType('font', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Font');

        //contains color picker and bg uploader
        $this->addType('background', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Background');

        $this->addType('color-picker', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ColorPicker');
        $this->addType('font-picker', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\FontPicker');
        $this->addType('logo-uploader', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\LogoUploader');
        $this->addType(
            'background-uploader',
            'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\BackgroundUploader'
        );

        return $this;
    }

    /**
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory $factory
     * @return $this
     */
    public function setRendererFactory($factory)
    {
        $this->_rendererFactory = $factory;
        return $this;
    }

    /**
     * @return \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer\Factory
     * @throws \Magento\Framework\Model\Exception
     */
    public function getRendererFactory()
    {
        if (!$this->_rendererFactory) {
            throw new \Magento\Framework\Model\Exception('Renderer factory was not set');
        }
        return $this->_rendererFactory;
    }

    /**
     * @param \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory $factory
     * @return $this
     */
    public function setElementsFactory($factory)
    {
        $this->_elementsFactory = $factory;
        return $this;
    }

    /**
     * @return \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Element\Factory
     * @throws \Magento\Framework\Model\Exception
     */
    public function getElementsFactory()
    {
        if (!$this->_elementsFactory) {
            throw new \Magento\Framework\Model\Exception('Form elements factory was not set');
        }
        return $this->_elementsFactory;
    }

    /**
     * Add fields to column element
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
        $element = $this->getElementsFactory()->create($className, $config);
        $element->setId($elementId);
        $this->addElement($element, $after);

        $layoutName = $element->getId() . '-renderer';
        $renderer = $this->getRendererFactory()->create($className, $layoutName);
        $element->setRenderer($renderer);
        $element->setAdvanced($isAdvanced);
        return $element;
    }
}
