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

namespace Magento\Eav\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Initialize helper
     */
    protected function setUp()
    {
        $context = $this->getMock('\Magento\App\Helper\Context', [], [], '', false);
        $attributeConfig = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Config', [], [], '', false);
        $coreStoreConfig = $this->getMock('\Magento\Core\Model\Store\Config', [], [], '', false);
        $eavConfig = $this->getMock('\Magento\Eav\Model\Config', [], [], '', false);
        $this->_helper = new Data($context, $attributeConfig, $coreStoreConfig, $eavConfig);
        $this->_eavConfig = $eavConfig;
    }

    public function testGetAttributeMetadata()
    {
        $attribute = new \Magento\Object([
            'entity_type_id' => '1',
            'attribute_id'   => '2',
            'backend'        => new \Magento\Object(['table' => 'customer_entity_varchar']),
            'backend_type'   => 'varchar'
        ]);
        $this->_eavConfig->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $result = $this->_helper->getAttributeMetadata('customer', 'lastname');
        $expected = [
            'entity_type_id' => '1',
            'attribute_id' => '2',
            'attribute_table' => 'customer_entity_varchar',
            'backend_type' => 'varchar'
        ];

        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $expected, 'Attribute metadata with key "' . $key . '" not found.');
            $this->assertEquals($expected[$key], $value,
                'Attribute metadata with key "' . $key . '" has invalid value.'
            );
        }
    }
}
