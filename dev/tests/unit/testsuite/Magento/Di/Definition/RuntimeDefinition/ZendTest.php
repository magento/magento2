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
 * @package     Magento_Di
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Di_Definition_RuntimeDefinition_ZendTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Class for test
     */
    const TEST_CLASS_NAME = 'stdClass';
    const TEST_CLASS_INSTANTIATOR = '__construct';
    /**#@-*/

    public function testGetInstantiator()
    {
        $generatorClass = $this->getMock('Magento_Di_Generator_Class');
        $generatorClass->expects($this->once())
            ->method('generateForConstructor')
            ->with(self::TEST_CLASS_NAME);

        $model = new Magento_Di_Definition_RuntimeDefinition_Zend(
            null,
            array(self::TEST_CLASS_NAME),
            $generatorClass
        );
        $this->assertEquals(self::TEST_CLASS_INSTANTIATOR, $model->getInstantiator(self::TEST_CLASS_NAME));
    }
}
