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
namespace Magento\Catalog\Service\V1\Product;

use Magento\Framework\Exception\NoSuchEntityException;

class GroupPriceServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GroupPriceService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModifierMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock(
            '\Magento\Catalog\Model\ProductRepository',
            array(),
            array(),
            '',
            false
        );
        $this->priceBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Data\Product\GroupPriceBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->storeManagerMock = $this->getMockBuilder('\Magento\Framework\StoreManagerInterface')
            ->setMethods(['getWebsite'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupServiceMock = $this->getMock('\Magento\Customer\Service\V1\CustomerGroupServiceInterface');

        $this->priceModifierMock =
            $this->getMock('Magento\Catalog\Model\Product\PriceModifier', array(), array(), '', false);
        $this->websiteMock =
            $this->getMock('Magento\Store\Model\Website', array('getId', '__wakeup'), array(), '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getData', 'setData', 'validate', 'save', 'getIdBySku', 'load', '__wakeup'),
            array(),
            '',
            false
        );
        $this->repositoryMock->expects($this->any())->method('get')->with('product_sku')
            ->will($this->returnValue($this->productMock));
        $this->configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->service = new GroupPriceService(
            $this->repositoryMock,
            $this->priceBuilderMock,
            $this->storeManagerMock,
            $this->groupServiceMock,
            $this->priceModifierMock,
            $this->configMock
        );
    }

    /**
     * @param string $configValue
     * @param array $groupData
     * @param array $expected
     * @dataProvider getListDataProvider
     */
    public function testGetList($configValue, $groupData, $expected)
    {
        $this->repositoryMock->expects($this->once())->method('get')->with('product_sku')
            ->will($this->returnValue($this->productMock));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue(array($groupData)));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue($configValue));
        $this->priceBuilderMock
            ->expects($this->once())
            ->method('populateWithArray')
            ->with($expected);
        $this->priceBuilderMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue('data'));
        $prices = $this->service->getList('product_sku');
        $this->assertCount(1, $prices);
        $this->assertEquals('data', $prices[0]);
    }

    public function getListDataProvider()
    {
        return array(
            array(
                1,
                array('website_price' => 10, 'price' => 5, 'all_groups' => 1),
                array('customer_group_id' => 'all', 'value' => 10)
            ),
            array(
                0,
                array('website_price' => 10, 'price' => 5, 'all_groups' => 0, 'cust_group' => 1),
                array('customer_group_id' => 1, 'value' => 5)
            )
        );
    }

    public function testSuccessDeleteGroupPrice()
    {
        $this->storeManagerMock
            ->expects($this->never())
            ->method('getWebsite');
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));
        $this->priceModifierMock->expects($this->once())->method('removeGroupPrice')->with($this->productMock, 4, 0);

        $this->assertEquals(true, $this->service->delete('product_sku', 4));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @message Such product doesn't exist
     */
    public function testDeleteGroupPriceFromNonExistingProduct()
    {
        $this->repositoryMock->expects($this->once())->method('get')
            ->will($this->throwException(new NoSuchEntityException()));
        $this->priceModifierMock->expects($this->never())->method('removeGroupPrice');
        $this->storeManagerMock
            ->expects($this->never())
            ->method('getWebsite');
        $this->service->delete('product_sku', null, 10);
    }

    public function testSuccessDeleteGroupPriceFromWebsiteLevel()
    {
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));
        $this->websiteMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(1));
        $this->priceModifierMock->expects($this->once())->method('removeGroupPrice')->with($this->productMock, 4, 1);

        $this->assertEquals(true, $this->service->delete('product_sku', 4));
    }

    public function testSetNewPriceWithGlobalPriceScope()
    {
        $priceBuilder = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Product\GroupPriceBuilder',
            array(),
            array(),
            '',
            false
        );
        $priceBuilder->expects($this->any())->method('getData')->will(
            $this->returnValue(
                array(
                    'customer_group_id' => 1,
                    'value' => 100
                )
            )
        );
        $price = new \Magento\Catalog\Service\V1\Data\Product\GroupPrice($priceBuilder);
        $groupBuilder = $this->getMock(
            '\Magento\Customer\Service\V1\Data\CustomerGroupBuilder',
            array(),
            array(),
            '',
            false
        );
        $groupBuilder->expects($this->any())->method('getData')->will($this->returnValue(array('id' => 1)));
        $group = new \Magento\Customer\Service\V1\Data\CustomerGroup($groupBuilder);
        $this->groupServiceMock->expects($this->once())->method('getGroup')->will($this->returnValue($group));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue(array(array('cust_group' => 2, 'website_id' => 0, 'price' => 50))));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(1));
        $this->storeManagerMock->expects($this->once())->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));
        $this->websiteMock->expects($this->once())->method('getId')
            ->will($this->returnValue(0));

        $this->productMock->expects($this->once())->method('setData')->with(
            'group_price',
            array(
                array('cust_group' => 2, 'website_id' => 0, 'price' => 50),
                array('cust_group' => 1, 'website_id' => 0, 'price' => 100)
            )
        );
        $this->productMock->expects($this->once())->method('save');
        $this->service->set('product_sku', $price);
    }

    public function testSetUpdatedPriceWithGlobalPriceScope()
    {
        $priceBuilder = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Product\GroupPriceBuilder',
            array(),
            array(),
            '',
            false
        );
        $priceBuilder->expects($this->any())->method('getData')->will(
            $this->returnValue(
                array(
                    'customer_group_id' => 2,
                    'value' => 100
                )
            )
        );
        $price = new \Magento\Catalog\Service\V1\Data\Product\GroupPrice($priceBuilder);
        $groupBuilder = $this->getMock(
            '\Magento\Customer\Service\V1\Data\CustomerGroupBuilder',
            array(),
            array(),
            '',
            false
        );
        $groupBuilder->expects($this->any())->method('getData')->will($this->returnValue(array('id' => 1)));
        $group = new \Magento\Customer\Service\V1\Data\CustomerGroup($groupBuilder);
        $this->groupServiceMock->expects($this->once())->method('getGroup')->will($this->returnValue($group));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue(array(array('cust_group' => 2, 'website_id' => 0, 'price' => 50))));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));

        $this->productMock->expects($this->once())->method('setData')->with(
            'group_price',
            array(
                array('cust_group' => 2, 'website_id' => 0, 'price' => 100),
            )
        );
        $this->productMock->expects($this->once())->method('save');
        $this->service->set('product_sku', $price);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Values of following attributes are invalid: attr1, attr2
     */
    public function testSetThrowsExceptionIfDoesntValidate()
    {
        $priceBuilder = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Product\GroupPriceBuilder',
            array(),
            array(),
            '',
            false
        );
        $priceBuilder->expects($this->any())->method('getData')->will(
            $this->returnValue(
                array(
                    'customer_group_id' => 2,
                    'value' => 100
                )
            )
        );
        $price = new \Magento\Catalog\Service\V1\Data\Product\GroupPrice($priceBuilder);
        $groupBuilder = $this->getMock(
            '\Magento\Customer\Service\V1\Data\CustomerGroupBuilder',
            array(),
            array(),
            '',
            false
        );
        $groupBuilder->expects($this->any())->method('getData')->will($this->returnValue(array('id' => 1)));
        $group = new \Magento\Customer\Service\V1\Data\CustomerGroup($groupBuilder);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue(array()));

        $this->groupServiceMock->expects($this->once())->method('getGroup')->will($this->returnValue($group));
        $this->productMock->expects($this->once())->method('validate')->will(
            $this->returnValue(
                array('attr1' => '', 'attr2' => '')
            )
        );
        $this->productMock->expects($this->never())->method('save');
        $this->service->set('product_sku', $price);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSetThrowsExceptionIfCantSave()
    {
        $priceBuilder = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Product\GroupPriceBuilder',
            array(),
            array(),
            '',
            false
        );
        $priceBuilder->expects($this->any())->method('getData')->will(
            $this->returnValue(
                array(
                    'customer_group_id' => 2,
                    'value' => 100
                )
            )
        );
        $price = new \Magento\Catalog\Service\V1\Data\Product\GroupPrice($priceBuilder);
        $groupBuilder = $this->getMock(
            '\Magento\Customer\Service\V1\Data\CustomerGroupBuilder',
            array(),
            array(),
            '',
            false
        );
        $groupBuilder->expects($this->any())->method('getData')->will($this->returnValue(array('id' => 1)));
        $group = new \Magento\Customer\Service\V1\Data\CustomerGroup($groupBuilder);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue(array()));

        $this->groupServiceMock->expects($this->once())->method('getGroup')->will($this->returnValue($group));
        $this->productMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->service->set('product_sku', $price);
    }
}
