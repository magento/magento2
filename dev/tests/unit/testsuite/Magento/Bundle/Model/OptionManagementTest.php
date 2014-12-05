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
 
namespace Magento\Bundle\Model;
 
class OptionManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionManagement
     */
    protected $model;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;
    
    protected function setUp()
    {
        $this->optionRepositoryMock =
            $this->getMock('Magento\Bundle\Api\ProductOptionRepositoryInterface', [], [], '', false);
        $this->productRepositoryMock =
            $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface', [], [], '', false);
        $this->optionMock = $this->getMock('Magento\Bundle\Api\Data\OptionInterface', [], [], '', false);
        $this->productMock = $this->getMock('Magento\Catalog\Api\Data\ProductInterface', [], [], '', false);

        $this->model = new OptionManagement($this->optionRepositoryMock, $this->productRepositoryMock);
    }

    public function testSave()
    {
        $this->optionMock->expects($this->once())->method('getSku')->willReturn('bundle_product_sku');
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with('bundle_product_sku')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->optionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock, $this->optionMock);

        $this->model->save($this->optionMock);
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionMessage Only implemented for bundle product
     */
    public function testSaveWithException()
    {
        $this->optionMock->expects($this->once())->method('getSku')->willReturn('bundle_product_sku');
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with('bundle_product_sku')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->optionRepositoryMock->expects($this->never())->method('save');

        $this->model->save($this->optionMock);
    }
}
