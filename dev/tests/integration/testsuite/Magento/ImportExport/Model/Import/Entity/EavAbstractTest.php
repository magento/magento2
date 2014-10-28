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
 * Test class for \Magento\ImportExport\Model\Import\Entity\AbstractEav
 */
namespace Magento\ImportExport\Model\Import\Entity;

class EavAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEav|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Create all necessary data for tests
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_model = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Import\Entity\AbstractEav',
            array(),
            '',
            false
        );
    }

    /**
     * Test for method getAttributeOptions()
     */
    public function testGetAttributeOptions()
    {
        $indexAttributeCode = 'gender';

        /** @var $attributeCollection \Magento\Customer\Model\Resource\Attribute\Collection */
        $attributeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Attribute\Collection'
        );
        $attributeCollection->addFieldToFilter(
            'attribute_code',
            array('in' => array($indexAttributeCode, 'group_id'))
        );
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($attributeCollection as $attribute) {
            $index = $attribute->getAttributeCode() == $indexAttributeCode ? 'value' : 'label';
            $expectedOptions = array();
            foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                $expectedOptions[strtolower($option[$index])] = $option['value'];
            }
            $actualOptions = $this->_model->getAttributeOptions($attribute, array($indexAttributeCode));
            sort($expectedOptions);
            sort($actualOptions);
            $this->assertEquals($expectedOptions, $actualOptions);
        }
    }
}
