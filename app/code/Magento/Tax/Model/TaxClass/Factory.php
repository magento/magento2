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

/**
 * Tax Class factory
 */
namespace Magento\Tax\Model\TaxClass;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Type to class map
     *
     * @var array
     */
    protected $_types = array(
        \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER => 'Magento\Tax\Model\TaxClass\Type\Customer',
        \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT => 'Magento\Tax\Model\TaxClass\Type\Product'
    );

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param \Magento\Tax\Model\ClassModel $taxClass
     * @return \Magento\Tax\Model\TaxClass\Type\TypeInterface
     * @throws \Magento\Framework\Model\Exception
     */
    public function create(\Magento\Tax\Model\ClassModel $taxClass)
    {
        $taxClassType = $taxClass->getClassType();
        if (!array_key_exists($taxClassType, $this->_types)) {
            throw new \Magento\Framework\Model\Exception(sprintf('Invalid type of tax class "%s"', $taxClassType));
        }
        return $this->_objectManager->create(
            $this->_types[$taxClassType],
            array('data' => array('id' => $taxClass->getId()))
        );
    }
}
