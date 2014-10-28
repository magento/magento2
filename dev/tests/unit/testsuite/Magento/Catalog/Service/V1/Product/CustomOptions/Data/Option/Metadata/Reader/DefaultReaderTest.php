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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Reader;

use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata;

class DefaultReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var DefaultReader
     */
    protected $service;

    protected function setUp()
    {
        $this->valueBuilderMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\MetadataBuilder', [], [], '', false
        );
        $this->optionMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Option', ['getPrice', 'getPriceType', 'getSku', '__wakeup'], [], '', false
        );
        $this->service = new DefaultReader($this->valueBuilderMock);
    }

    public function testRead()
    {
        $this->optionMock->expects($this->once())->method('getPrice')->will($this->returnValue('10'));
        $this->optionMock->expects($this->once())->method('getPriceType')->will($this->returnValue('USD'));
        $this->optionMock->expects($this->once())->method('getSku')->will($this->returnValue('product_sku'));
        $fields = [
            Metadata::PRICE => '10',
            Metadata::PRICE_TYPE => 'USD' ,
            Metadata::SKU => 'product_sku'
        ];
        $this->valueBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($fields)
            ->will($this->returnValue($this->valueBuilderMock));
        $this->valueBuilderMock->expects($this->once())->method('create')->will($this->returnValue('Expected value'));
        $this->assertEquals(array('Expected value'), $this->service->read($this->optionMock));
    }
}
