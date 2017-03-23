<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Standard library element types
     *
     * @var string[]
     */
    protected $_standardTypes = [
        'button',
        'checkbox',
        'checkboxes',
        'column',
        'date',
        'editablemultiselect',
        'editor',
        'fieldset',
        'file',
        'gallery',
        'hidden',
        'image',
        'imagefile',
        'label',
        'link',
        'multiline',
        'multiselect',
        'note',
        'obscure',
        'password',
        'radio',
        'radios',
        'reset',
        'select',
        'submit',
        'text',
        'textarea',
        'time',
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Factory method
     *
     * @param string $elementType Standard element type or Custom element class
     * @param array $config
     * @return AbstractElement
     * @throws \InvalidArgumentException
     */
    public function create($elementType, array $config = [])
    {
        if (in_array($elementType, $this->_standardTypes)) {
            $className = 'Magento\Framework\Data\Form\Element\\' . ucfirst($elementType);
        } else {
            $className = $elementType;
        }

        $element = $this->_objectManager->create($className, $config);
        if (!$element instanceof AbstractElement) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'n extend \Magento\Framework\Data\Form\Element\AbstractElement'
            );
        }
        return $element;
    }
}
