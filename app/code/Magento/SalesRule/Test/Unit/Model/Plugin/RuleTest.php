<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\Plugin;

class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Plugin\Rule
     */
    protected $plugin;

    /**}
     * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Closure
     */
    protected $genericClosure;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->subject = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericClosure = function () {
            return;
        };

        $this->plugin = $objectManager->getObject(\Magento\SalesRule\Model\Plugin\Rule::class);
    }

    public function testLoadRelations()
    {
        $this->assertEquals(
            $this->subject,
            $this->plugin->aroundLoadRelations($this->subject, $this->genericClosure)
        );
    }
}
