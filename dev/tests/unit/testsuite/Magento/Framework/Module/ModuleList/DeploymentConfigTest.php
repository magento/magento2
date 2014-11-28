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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Module\ModuleList;


class DeploymentConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetKey()
    {
        $object = new DeploymentConfig([]);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $object = new DeploymentConfig([]);
        $this->assertSame([], $object->getData());
        $object = new DeploymentConfig(['Module_One' => '1', 'Module_Two' => false]);
        $this->assertSame(['Module_One' => 1, 'Module_Two' => 0], $object->getData());
    }

    /**
     * @param array $data
     * @dataProvider invalidDataDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect module name:
     */
    public function testInvalidData($data)
    {
        new DeploymentConfig($data);
    }

    /**
     * @return array
     */
    public function invalidDataDataProvider()
    {
        return [
            [['1', '2']],
            [['invalid_module' => 1]],
        ];
    }
}
