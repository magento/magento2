<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Website
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var WebsiteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteFactory;

    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->websiteFactory = $this->getMockBuilder('Magento\Store\Model\WebsiteFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getCollection', '__wakeup'])
            ->getMock();

        /** @var Website $websiteModel */
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Website',
            ['websiteFactory' => $this->websiteFactory]
        );
    }

    public function testIsCanDelete()
    {
        $websiteCollection = $this->getMock(
            'Magento\Store\Model\ResourceModel\Website\Collection',
            ['getSize'],
            [],
            '',
            false
        );
        $websiteCollection->expects($this->any())->method('getSize')->will($this->returnValue(2));

        $this->websiteFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->websiteFactory);
        $this->websiteFactory->expects($this->any())
            ->method('getCollection')
            ->willReturn($websiteCollection);

        $this->model->setId(2);
        $this->assertTrue($this->model->isCanDelete());
    }

    public function testGetScopeType()
    {
        $this->assertEquals(ScopeInterface::SCOPE_WEBSITE, $this->model->getScopeType());
    }

    public function testGetScopeTypeName()
    {
        $this->assertEquals('Website', $this->model->getScopeTypeName());
    }
}
