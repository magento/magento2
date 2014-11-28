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
namespace Magento\Catalog\Model\Product;

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Exception\NoSuchEntityException;

class TierPriceManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TierPriceManagement
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

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
    protected $priceModifierMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

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
            'Magento\Catalog\Api\Data\ProductTierPriceDataBuilder',
            array('populateWithArray', 'create'),
            array(),
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $this->websiteMock =
            $this->getMock('Magento\Store\Model\Website', array('getId', '__wakeup'), array(), '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getData', 'getIdBySku', 'load', '__wakeup', 'save', 'validate', 'setData'),
            array(),
            '',
            false
        );
        $this->configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->priceModifierMock =
            $this->getMock('Magento\Catalog\Model\Product\PriceModifier', array(), array(), '', false);
        $this->repositoryMock->expects($this->any())->method('get')->with('product_sku')
            ->will($this->returnValue($this->productMock));
        $this->groupManagementMock =
            $this->getMock('Magento\Customer\Api\GroupManagementInterface', array(), array(), '', false);
        $this->groupRepositoryMock =
            $this->getMock('Magento\Customer\Api\GroupRepositoryInterface', array(), array(), '', false);

        $this->service = new TierPriceManagement(
            $this->repositoryMock,
            $this->priceBuilderMock,
            $this->storeManagerMock,
            $this->priceModifierMock,
            $this->configMock,
            $this->groupManagementMock,
            $this->groupRepositoryMock
        );
    }

    /**
     * @param $configValue
     * @param $customerGroupId
     * @param $groupData
     * @param $expected
     * @dataProvider getListDataProvider
     */
    public function testGetList($configValue, $customerGroupId, $groupData, $expected)
    {
        $this->repositoryMock->expects($this->once())->method('get')->with('product_sku')
            ->will($this->returnValue($this->productMock));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue(array($groupData)));
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue($configValue));
        if ($expected) {
            $this->priceBuilderMock
                ->expects($this->once())
                ->method('populateWithArray')
                ->with($expected);
            $this->priceBuilderMock
                ->expects($this->once())
                ->method('create')
                ->will($this->returnValue('data'));
        } else {
            $this->priceBuilderMock->expects($this->never())->method('populateWithArray');
        }
        $prices = $this->service->getList('product_sku', $customerGroupId);
        $this->assertCount($expected ? 1 : 0, $prices);
        if ($expected) {
            $this->assertEquals('data', $prices[0]);
        }
    }

    public function getListDataProvider()
    {
        return array(
            array(
                1,
                'all',
                array('website_price' => 10, 'price' => 5, 'all_groups' => 1, 'price_qty' => 5),
                array('value' => 10, 'qty' => 5)
            ),
            array(
                0,
                1,
                array('website_price' => 10, 'price' => 5, 'all_groups' => 0, 'cust_group' => 1, 'price_qty' => 5),
                array('value' => 5, 'qty' => 5)
            ),
            array(
                0,
                'all',
                array('website_price' => 10, 'price' => 5, 'all_groups' => 0, 'cust_group' => 1, 'price_qty' => 5),
                array()
            )
        );
    }

    public function testSuccessDeleteTierPrice()
    {
        $this->storeManagerMock
            ->expects($this->never())
            ->method('getWebsite');
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));
        $this->priceModifierMock->expects($this->once())->method('removeTierPrice')->with($this->productMock, 4, 5, 0);

        $this->assertEquals(true, $this->service->remove('product_sku', 4, 5, 0));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @message Such product doesn't exist
     */
    public function testDeleteTierPriceFromNonExistingProduct()
    {
        $this->repositoryMock->expects($this->once())->method('get')
            ->will($this->throwException(new NoSuchEntityException()));
        $this->priceModifierMock->expects($this->never())->method('removeTierPrice');
        $this->storeManagerMock
            ->expects($this->never())
            ->method('getWebsite');
        $this->service->remove('product_sku', null, 10, 5);
    }

    public function testSuccessDeleteTierPriceFromWebsiteLevel()
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
        $this->priceModifierMock->expects($this->once())->method('removeTierPrice')->with($this->productMock, 4, 5, 1);

        $this->assertEquals(true, $this->service->remove('product_sku', 4, 5, 6));
    }

    public function testSetNewPriceWithGlobalPriceScopeAll()
    {
        $websiteMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())->method('getId')->will($this->returnValue(0));

        $this->storeManagerMock->expects($this->once())->method('getWebsite')->will($this->returnValue($websiteMock));

        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will(
                $this->returnValue(
                    array(array('all_groups' => 0, 'website_id' => 0, 'price_qty' => 4, 'price' => 50))
                )
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(1));

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            array(
                array('all_groups' => 0, 'website_id' => 0, 'price_qty' => 4, 'price' => 50),
                array(
                    'cust_group' => 32000,
                    'price' => 100,
                    'website_price' => 100,
                    'website_id' => 0,
                    'price_qty' => 3
                )
            )
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $group = $this->getMock('\Magento\Customer\Model\Data\Group',
            array(),
            array(),
            '',
            false
        );
        $group->expects($this->once())->method('getId')->willReturn(GroupManagement::CUST_GROUP_ALL);
        $this->groupManagementMock->expects($this->once())->method('getAllCustomersGroup')
            ->will($this->returnValue($group));
        $this->service->add('product_sku', 'all', 100, 3);
    }

    public function testSetNewPriceWithGlobalPriceScope()
    {
        $group = $this->getMock('\Magento\Customer\Model\Data\Group', [], [], '', false);
        $group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->groupRepositoryMock->expects($this->once())->method('getById')->will($this->returnValue($group));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will(
                $this->returnValue(
                    array(array('cust_group' => 1, 'website_id' => 0, 'price_qty' => 4, 'price' => 50))
                )
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            array(
                array('cust_group' => 1, 'website_id' => 0, 'price_qty' => 4, 'price' => 50),
                array('cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 100, 'website_price' => 100)
            )
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->service->add('product_sku', 1, 100, 3);
    }

    public function testSetUpdatedPriceWithGlobalPriceScope()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will(
                $this->returnValue(
                    array(array('cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 50))
                )
            );
        $this->configMock
            ->expects($this->once())
            ->method('getValue')
            ->with('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)
            ->will($this->returnValue(0));

        $this->productMock->expects($this->once())->method('setData')->with(
            'tier_price',
            array(
                array('cust_group' => 1, 'website_id' => 0, 'price_qty' => 3, 'price' => 100)
            )
        );
        $this->repositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->service->add('product_sku', 1, 100, 3);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Values of following attributes are invalid: attr1, attr2
     */
    public function testSetThrowsExceptionIfDoesntValidate()
    {
        $group = $this->getMock('\Magento\Customer\Model\Data\Group', [], [], '', false);
        $group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue(array()));

        $this->groupRepositoryMock->expects($this->once())->method('getById')->will($this->returnValue($group));
        $this->productMock->expects($this->once())->method('validate')->will(
            $this->returnValue(
                array('attr1' => '', 'attr2' => '')
            )
        );
        $this->repositoryMock->expects($this->never())->method('save');
        $this->service->add('product_sku', 1, 100, 2);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSetThrowsExceptionIfCantSave()
    {
        $group = $this->getMock('\Magento\Customer\Model\Data\Group', [], [], '', false);
        $group->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue(array()));

        $this->groupRepositoryMock->expects($this->once())->method('getById')->will($this->returnValue($group));
        $this->repositoryMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->service->add('product_sku', 1, 100, 2);
    }

    /**
     * @param string|int $price
     * @param string|float $qty
     * @expectedException \Magento\Framework\Exception\InputException
     * @dataProvider addDataProvider
     */

    public function testAddWithInvalidData($price, $qty)
    {
        $this->service->add('product_sku', 1, $price, $qty);
    }

    public function addDataProvider()
    {
        return [
            ['string', 10],
            [10, '10string'],
            [10, -15]
        ];
    }
} 
