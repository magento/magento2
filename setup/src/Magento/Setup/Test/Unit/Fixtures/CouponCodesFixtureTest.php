<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CouponCodesFixture;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponCodesFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Fixtures\CartPriceRulesFixture
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $couponCodeFactoryMock;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->fixtureModelMock = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);
        $this->ruleFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->couponCodeFactoryMock = $this->createPartialMock(
            \Magento\SalesRule\Model\CouponFactory::class,
            ['create']
        );
        $this->model = new CouponCodesFixture(
            $this->fixtureModelMock,
            $this->ruleFactoryMock,
            $this->couponCodeFactoryMock
        );
    }

    /**
     * testExecute
     */
    public function testExecute()
    {
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue(2));

        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));
        $websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $abstractDbMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/file'));
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('category_id'));

        $objectValueMap = [
            [\Magento\Catalog\Model\Category::class, $categoryMock]
        ];

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->will($this->returnValueMap($objectValueMap));

        $valueMap = [
            ['coupon_codes', 0, 1]
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(1))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $couponMock = $this->createMock(\Magento\SalesRule\Model\Coupon::class);
        $couponMock->expects($this->once())
            ->method('setRuleId')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('setCode')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('setIsPrimary')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('setType')
            ->willReturnSelf();
        $couponMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->couponCodeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($couponMock);

        $this->model->execute();
    }

    /**
     * testNoFixtureConfigValue
     */
    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo(\Magento\SalesRule\Model\Rule::class))
            ->willReturn($ruleMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    /**
     * testGetActionTitle
     */
    public function testGetActionTitle()
    {
        $this->assertSame('Generating coupon codes', $this->model->getActionTitle());
    }

    /**
     * testIntroduceParamLabels
     */
    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'coupon_codes' => 'Coupon Codes'
        ], $this->model->introduceParamLabels());
    }
}
