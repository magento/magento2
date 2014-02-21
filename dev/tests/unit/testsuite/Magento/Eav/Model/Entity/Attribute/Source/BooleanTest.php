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
 * @package     Magento_Eav
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Eav\Model\Entity\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\Boolean
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\Eav\Model\Entity\Attribute\Source\Boolean');
    }

    public function testGetFlatColums()
    {
        $abstractAttributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array('getAttributeCode', '__wakeup'), array(), '', false
        );

        $abstractAttributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('code'));

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColums = $this->_model->getFlatColums();

        $this->assertTrue(is_array($flatColums), 'FlatColums must be an array value');
        $this->assertTrue(!empty($flatColums), 'FlatColums must be not empty');
        foreach ($flatColums as $result) {
            $this->assertArrayHasKey('unsigned', $result, 'FlatColums must have "unsigned" column');
            $this->assertArrayHasKey('default', $result, 'FlatColums must have "default" column');
            $this->assertArrayHasKey('extra', $result, 'FlatColums must have "extra" column');
            $this->assertArrayHasKey('type', $result, 'FlatColums must have "type" column');
            $this->assertArrayHasKey('nullable', $result, 'FlatColums must have "nullable" column');
            $this->assertArrayHasKey('comment', $result, 'FlatColums must have "comment" column');
            $this->assertArrayHasKey('length', $result, 'FlatColums must have "length" column');
        }
    }
}
