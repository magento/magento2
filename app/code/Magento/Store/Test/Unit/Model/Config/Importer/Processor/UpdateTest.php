<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Processor\Update;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Magento\Store\Model\Store;

/**
 * Test for Update processor.
 *
 * @see Update
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Update
     */
    private $model;

    /**
     * @var Group|Mock
     */
    private $groupMock;

    /**
     * @var GroupResource|Mock
     */
    private $groupResourceMock;

    /**
     * @var Website|Mock
     */
    private $websiteMock;

    /**
     * @var WebsiteResource|Mock
     */
    private $websiteResourceMock;

    /**
     * @var DataDifferenceCalculator|Mock
     */
    private $dataDifferenceCalculatorMock;

    /**
     * @var WebsiteFactory|Mock
     */
    private $websiteFactoryMock;

    /**
     * @var StoreFactory|Mock
     */
    private $storeFactoryMock;

    /**
     * @var GroupFactory|Mock
     */
    private $groupFactoryMock;

    /**
     * @var Store|Mock
     */
    private $storeMock;

    /**
     * @var StoreResource|Mock
     */
    private $storeResourceMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteFactoryMock = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteResourceMock = $this->getMockBuilder(WebsiteResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupFactoryMock = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupResourceMock = $this->getMockBuilder(GroupResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeResourceMock = $this->getMockBuilder(StoreResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->storeResourceMock);
        $this->websiteMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->websiteResourceMock);

        $this->model = new Update(
            $this->dataDifferenceCalculatorMock,
            $this->websiteFactoryMock,
            $this->storeFactoryMock,
            $this->groupFactoryMock
        );
    }

    public function testRun()
    {
        $data = [
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_STORES => [],
        ];
        $updateData = [
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_STORES => [],
        ];

        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToUpdate')
            ->willReturnMap([
                [
                    ScopeInterface::SCOPE_GROUPS,
                    $data[ScopeInterface::SCOPE_GROUPS],
                    $updateData[ScopeInterface::SCOPE_GROUPS],
                ],
                [
                    ScopeInterface::SCOPE_WEBSITES,
                    $data[ScopeInterface::SCOPE_WEBSITES],
                    $updateData[ScopeInterface::SCOPE_WEBSITES],
                ],
                [
                    ScopeInterface::SCOPE_STORES,
                    $data[ScopeInterface::SCOPE_STORES],
                    $updateData[ScopeInterface::SCOPE_STORES],
                ],
            ]);

        $this->model->run($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Some exception
     */
    public function testRunWithException()
    {
        $data = [
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_STORES => []
        ];

        $this->dataDifferenceCalculatorMock->expects($this->once())
            ->method('getItemsToUpdate')
            ->willThrowException(new \Exception('Some exception'));

        $this->model->run($data);
    }
}
