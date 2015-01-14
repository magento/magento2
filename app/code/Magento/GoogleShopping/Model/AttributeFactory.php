<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

/**
 * Attributes Factory
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class AttributeFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * GoogleShopping data
     *
     * @var \Magento\GoogleShopping\Helper\Data
     */
    protected $_googleShoppingHelper;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $_string;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\GoogleShopping\Helper\Data $googleShoppingHelper
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\GoogleShopping\Helper\Data $googleShoppingHelper,
        \Magento\Framework\Stdlib\String $string
    ) {
        $this->_objectManager = $objectManager;
        $this->_googleShoppingHelper = $googleShoppingHelper;
        $this->_string = $string;
    }

    /**
     * Create attribute model
     *
     * @param string $name
     * @return \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
     */
    public function createAttribute($name)
    {
        $modelName = 'Magento\GoogleShopping\Model\Attribute\\' . $this->_string->upperCaseWords(
            $this->_googleShoppingHelper->normalizeName($name)
        );
        try {
            /** @var \Magento\GoogleShopping\Model\Attribute\DefaultAttribute $attributeModel */
            $attributeModel = $this->_objectManager->create($modelName);
            if (!$attributeModel) {
                $attributeModel = $this->_objectManager->create(
                    'Magento\GoogleShopping\Model\Attribute\DefaultAttribute'
                );
            }
        } catch (\Exception $e) {
            $attributeModel = $this->_objectManager->create('Magento\GoogleShopping\Model\Attribute\DefaultAttribute');
        }

        $attributeModel->setName($name);
        return $attributeModel;
    }

    /**
     * Create attribute model
     *
     * @return \Magento\GoogleShopping\Model\Attribute
     */
    public function create()
    {
        return $this->_objectManager->create('Magento\GoogleShopping\Model\Attribute');
    }
}
