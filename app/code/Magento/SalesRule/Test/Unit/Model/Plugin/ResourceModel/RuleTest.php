<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Plugin\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Plugin\ResourceModel\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $ruleResource;

    /**
     * @var \Closure
     */
    protected $genericClosure;

    /**
     * @var MockObject
     */
    protected $abstractModel;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->ruleResource = $this->getMockBuilder(\Magento\SalesRule\Model\ResourceModel\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericClosure = function () {
            return;
        };
        $this->abstractModel = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->plugin = $objectManager->getObject(Rule::class);
    }

    public function testAroundLoadCustomerGroupIds()
    {
        $this->assertEquals(
            $this->ruleResource,
            $this->plugin->aroundLoadCustomerGroupIds($this->ruleResource, $this->genericClosure, $this->abstractModel)
        );
    }

    public function testAroundLoadWebsiteIds()
    {
        $this->assertEquals(
            $this->ruleResource,
            $this->plugin->aroundLoadWebsiteIds($this->ruleResource, $this->genericClosure, $this->abstractModel)
        );
    }
}
