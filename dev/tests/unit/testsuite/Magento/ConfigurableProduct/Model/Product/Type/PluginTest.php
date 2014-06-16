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
namespace Magento\ConfigurableProduct\Model\Product\Type;

/**
 * Class \Magento\ConfigurableProduct\Model\Product\Type\PluginTest
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $expected
     * @param array $data
     * @dataProvider afterGetOptionArrayDataProvider
     */
    public function testAfterGetOptionArray(array $expected, array $data)
    {
        $moduleManagerMock = $this->getMock(
            'Magento\Framework\Module\Manager', array('isOutputEnabled'), array(), '', false
        );
        $moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_ConfigurableProduct')
            ->will($this->returnValue($data['is_module_output_enabled']));

        $model = new \Magento\ConfigurableProduct\Model\Product\Type\Plugin($moduleManagerMock);
        $this->assertEquals(
            $expected,
            $model->afterGetOptionArray($data['subject'], $data['result'])
        );
    }

    /**
     * @return array
     */
    public function afterGetOptionArrayDataProvider()
    {
        $productTypeMock = $this->getMock('Magento\Catalog\Model\Product\Type', array(), array(), '', false);
        return array(
            array(
                array(
                    'configurable' => true,
                    'not_configurable' => true
                ),
                array(
                    'is_module_output_enabled' => true,
                    'subject' => $productTypeMock,
                    'result' => array(
                        'configurable' => true,
                        'not_configurable' => true
                    )
                )
            ),
            array(
                array(
                    'not_configurable' => true
                ),
                array(
                    'is_module_output_enabled' => false,
                    'subject' => $productTypeMock,
                    'result' => array(
                        'configurable' => true,
                        'not_configurable' => true
                    )
                )
            )
        );
    }
}
