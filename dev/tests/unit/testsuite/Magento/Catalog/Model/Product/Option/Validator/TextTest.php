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

namespace Magento\Catalog\Model\Product\Option\Validator;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\Text
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueMock;

    protected function setUp()
    {
        $configMock = $this->getMock('Magento\Catalog\Model\ProductOptions\ConfigInterface');
        $priceConfigMock = new \Magento\Catalog\Model\Config\Source\Product\Options\Price();
        $config = [
            [
                'label' => 'group label 1',
                'types' => [
                    [
                        'label' => 'label 1.1',
                        'name' => 'name 1.1',
                        'disabled' => false
                    ],
                ]
            ],
            [
                'label' => 'group label 2',
                'types' => [
                    [
                        'label' => 'label 2.2',
                        'name' => 'name 2.2',
                        'disabled' => true
                    ],
                ]
            ],
        ];
        $configMock->expects($this->once())->method('getAll')->will($this->returnValue($config));
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getMaxCharacters'];
        $this->valueMock = $this->getMock('Magento\Catalog\Model\Product\Option', $methods, [], '', false);
        $this->validator = new \Magento\Catalog\Model\Product\Option\Validator\Text(
            $configMock,
            $priceConfigMock
        );
    }

    public function testIsValidSuccess()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('fixed'));
        $this->valueMock->expects($this->once())->method('getPrice')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getMaxCharacters')->will($this->returnValue(10));
        $this->assertTrue($this->validator->isValid($this->valueMock));
        $this->assertEmpty($this->validator->getMessages());
    }

    public function testIsValidWithNegativeMaxCharacters()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('fixed'));
        $this->valueMock->expects($this->once())->method('getPrice')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getMaxCharacters')->will($this->returnValue(-10));
        $messages = [
            'option values' => 'Invalid option value'
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
