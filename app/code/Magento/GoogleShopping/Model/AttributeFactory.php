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
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleShopping\Model;

/**
 * Attributes Factory
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class AttributeFactory
{
    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * GoogleShopping data
     *
     * @var \Magento\GoogleShopping\Helper\Data
     */
    protected $_gsData;

    /**
     * @var \Magento\Stdlib\String
     */
    protected $_string;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\GoogleShopping\Helper\Data $gsData
     * @param \Magento\Stdlib\String $string
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\GoogleShopping\Helper\Data $gsData,
        \Magento\Stdlib\String $string
    ) {
        $this->_objectManager = $objectManager;
        $this->_gsData = $gsData;
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
        $modelName = 'Magento\GoogleShopping\Model\Attribute\\'
            . $this->_string->upperCaseWords($this->_gsData->normalizeName($name));
        try {
            /** @var \Magento\GoogleShopping\Model\Attribute\DefaultAttribute $attributeModel */
            $attributeModel = $this->_objectManager->create($modelName);
            if (!$attributeModel) {
                $attributeModel = $this->_objectManager
                    ->create('Magento\GoogleShopping\Model\Attribute\DefaultAttribute');
            }
        } catch (\Exception $e) {
            $attributeModel = $this->_objectManager
                ->create('Magento\GoogleShopping\Model\Attribute\DefaultAttribute');
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
