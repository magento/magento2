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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Reader;

use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Select
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    protected function setUp()
    {
        $this->valueBuilderMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\MetadataBuilder', [], [], '', false
        );
        $this->optionMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Option', [], [], '', false
        );
        $this->service = new Select($this->valueBuilderMock);
    }

    public function testRead()
    {
        $valueMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Option',
            ['getPrice', 'getPriceType', 'getSku', 'getTitle', 'getSortOrder', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $this->optionMock->expects($this->any())->method('getValues')->will($this->returnValue(array($valueMock)));
        $valueMock->expects($this->once())->method('getPrice')->will($this->returnValue('35'));
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('USD'));
        $valueMock->expects($this->once())->method('getSku')->will($this->returnValue('product_sku'));
        $valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('Some Title'));
        $valueMock->expects($this->once())->method('getSortOrder')->will($this->returnValue('0'));
        $valueMock->expects($this->once())->method('getId')->will($this->returnValue('12345678'));
        $fields = [
            Metadata::PRICE => '35',
            Metadata::PRICE_TYPE => 'USD' ,
            Metadata::SKU => 'product_sku',
            Metadata::TITLE => 'Some Title',
            Metadata::SORT_ORDER => '0',
            Metadata::OPTION_TYPE_ID => '12345678'
        ];
        $this->valueBuilderMock
            ->expects($this->any())->method('populateWithArray')
            ->with($fields)
            ->will($this->returnValue($this->valueBuilderMock));
        $this->valueBuilderMock->expects($this->once())->method('create')->will($this->returnValue($fields));
        $this->assertEquals(array($fields), $this->service->read($this->optionMock));
    }
}
