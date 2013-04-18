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
 * @package     Mage_Webapi
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for Mage_Webapi_Model_Source_Acl_Role.
 */
class Mage_Webapi_Model_Source_Acl_RoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check output format.
     *
     * @dataProvider toOptionsHashDataProvider
     *
     * @param bool $addEmpty
     * @param array $data
     * @param array $expected
     */
    public function testToOptionHashFormat($addEmpty, $data, $expected)
    {
        $resourceMock = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_Role')
            ->setMethods(array('getRolesList'))
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getRolesList')
            ->will($this->returnValue($data));

        $model = new Mage_Webapi_Model_Source_Acl_Role(array(
            'resource' => $resourceMock
        ));

        $options = $model->toOptionHash($addEmpty);
        $this->assertEquals($expected, $options);
    }

    /**
     * Data provider for testing toOptionHash.
     *
     * @return array
     */
    public function toOptionsHashDataProvider()
    {
        return array(
            'with empty' => array(
                true, array('1' => 'role 1', '2' => 'role 2'), array('' => '', '1' => 'role 1', '2' => 'role 2')
            ),
            'without empty' => array(
                false, array('1' => 'role 1', '2' => 'role 2'), array('1' => 'role 1', '2' => 'role 2')
            ),
        );
    }
}
