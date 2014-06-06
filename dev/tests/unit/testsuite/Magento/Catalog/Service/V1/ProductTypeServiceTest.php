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
namespace Magento\Catalog\Service\V1;

class ProductTypeServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductTypeService
     */
    private $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeBuilderMock;

    protected function setUp()
    {
        $this->typeConfigMock = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->typeBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Data\ProductTypeBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->service = new ProductTypeService(
            $this->typeConfigMock,
            $this->typeBuilderMock
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
        $productTypeMock = $this->getMock(
            'Magento\Catalog\Service\V1\Data\ProductType',
            array(),
            array(),
            '',
            false
        );
        $this->typeConfigMock->expects($this->any())->method('getAll')->will($this->returnValue($productTypeData));
        $this->typeBuilderMock->expects($this->once())
            ->method('setName')
            ->with($simpleProductType['name'])
            ->will($this->returnSelf());
        $this->typeBuilderMock->expects($this->once())
            ->method('setLabel')
            ->with($simpleProductType['label'])
            ->will($this->returnSelf());
        $this->typeBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($productTypeMock));

        $productTypes = $this->service->getProductTypes();
        $this->assertCount(1, $productTypes);
        $this->assertContains($productTypeMock, $productTypes);
    }
}
