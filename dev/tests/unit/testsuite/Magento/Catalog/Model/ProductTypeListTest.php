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
namespace Magento\Catalog\Model;

class ProductTypeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductTypeList
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builderMock;

    protected function setUp()
    {
        $this->typeConfigMock = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->builderMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductTypeDataBuilder',
            array('create', 'populateWithArray'),
            array(),
            '',
            false
        );
        $this->model = new ProductTypeList(
            $this->typeConfigMock,
            $this->builderMock
        );
    }

    public function testGetProductTypes()
    {
        $simpleProductType = array(
            'name' => 'simple',
            'label' => 'Simple Product',
        );
        $productTypeData = array(
            'simple' => $simpleProductType,
        );
        $productTypeMock = $this->getMock('Magento\Catalog\Api\Data\ProductTypeInterface');
        $this->typeConfigMock->expects($this->any())->method('getAll')->will($this->returnValue($productTypeData));
        $this->builderMock->expects($this->once())
            ->method('populateWithArray')
            ->with(array(
                'name' => $simpleProductType['name'],
                'label' => $simpleProductType['label'],
            ))->willReturnSelf();

        $this->builderMock->expects($this->once())->method('create')->willReturn($productTypeMock);
        $productTypes = $this->model->getProductTypes();
        $this->assertCount(1, $productTypes);
        $this->assertContains($productTypeMock, $productTypes);
    }
}
