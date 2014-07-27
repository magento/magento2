<?php
/**
 *
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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;


    protected function setUp()
    {
        $this->metadataConverterMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ConverterInterface'
        );
        $this->optionMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false
        );

        $this->service = new Converter($this->metadataConverterMock);
    }

    public function testConvert()
    {
        $this->optionMock->expects($this->any())->method('getOptionId')->will($this->returnValue('123456'));
        $this->optionMock->expects($this->any())->method('getTitle')->will($this->returnValue('Some Title'));
        $this->optionMock->expects($this->any())->method('getType')->will($this->returnValue('Type'));
        $this->optionMock->expects($this->any())->method('getSortOrder')->will($this->returnValue('12'));
        $this->optionMock->expects($this->any())->method('getIsRequire')->will($this->returnValue(true));
        $options = [
            'option_id' => '123456',
            'title' => 'Some Title',
            'type' => 'Type',
            'sort_order' => '12',
            'is_require' => true
        ];
        $this->metadataConverterMock
            ->expects($this->once())
            ->method('convert')
            ->with($this->optionMock)
            ->will($this->returnValue($options));
        $this->assertEquals($options, $this->service->convert($this->optionMock));
    }
}
