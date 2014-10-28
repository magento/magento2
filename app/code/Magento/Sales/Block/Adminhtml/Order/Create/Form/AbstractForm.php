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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Sales Order Create Form Abstract Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractForm extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * Data Form object
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = array()
    ) {
        $this->_formFactory = $formFactory;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Prepare global layout
     * Add renderers to \Magento\Framework\Data\Form
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Element',
                $this->getNameInLayout() . '_element'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset',
                $this->getNameInLayout() . '_fieldset'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Return Form object
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        if (is_null($this->_form)) {
            $this->_form = $this->_formFactory->create();
            $this->_prepareForm();
        }
        return $this->_form;
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    abstract protected function _prepareForm();

    /**
     * Return array of additional form element types by type
     *
     * @return array
     */
    protected function _getAdditionalFormElementTypes()
    {
        return array(
            'file' => 'Magento\Customer\Block\Adminhtml\Form\Element\File',
            'image' => 'Magento\Customer\Block\Adminhtml\Form\Element\Image',
            'boolean' => 'Magento\Customer\Block\Adminhtml\Form\Element\Boolean'
        );
    }

    /**
     * Return array of additional form element renderers by element id
     *
     * @return array
     */
    protected function _getAdditionalFormElementRenderers()
    {
        return array(
            'region' => $this->getLayout()->createBlock('Magento\Customer\Block\Adminhtml\Edit\Renderer\Region')
        );
    }

    /**
     * Add additional data to form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this;
    }

    /**
     * Add rendering EAV attributes to Form element
     *
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[] $attributes
     * @param \Magento\Framework\Data\Form\AbstractForm $form
     * @return $this
     */
    protected function _addAttributesToForm($attributes, \Magento\Framework\Data\Form\AbstractForm $form)
    {
        // add additional form types
        $types = $this->_getAdditionalFormElementTypes();
        foreach ($types as $type => $className) {
            $form->addType($type, $className);
        }
        $renderers = $this->_getAdditionalFormElementRenderers();

        foreach ($attributes as $attribute) {
            $inputType = $attribute->getFrontendInput();

            if ($inputType) {
                $element = $form->addField(
                    $attribute->getAttributeCode(),
                    $inputType,
                    array(
                        'name' => $attribute->getAttributeCode(),
                        'label' => __($attribute->getStoreLabel()),
                        'class' => $attribute->getFrontendClass(),
                        'required' => $attribute->isRequired()
                    )
                );
                if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
                $element->setEntityAttribute($attribute);
                $this->_addAdditionalFormElementData($element);

                if (!empty($renderers[$attribute->getAttributeCode()])) {
                    $element->setRenderer($renderers[$attribute->getAttributeCode()]);
                }

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $options = array();
                    foreach ($attribute->getOptions() as $optionData) {
                        $options[] = \Magento\Framework\Service\SimpleDataObjectConverter::toFlatArray($optionData);
                    }
                    $element->setValues($options);
                } elseif ($inputType == 'date') {
                    $format = $this->_localeDate->getDateFormat(
                        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
                    );
                    $element->setImage($this->getViewFileUrl('images/grid-cal.gif'));
                    $element->setDateFormat($format);
                }
            }
        }

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return array();
    }
}
