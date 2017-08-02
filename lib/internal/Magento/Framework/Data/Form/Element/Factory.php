<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\Data\Form\Element\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Standard library element types
     *
     * @var string[]
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
