<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Plugin\Rule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    protected $plugin;

    /**}
     * @var \Magento\SalesRule\Model\Rule|MockObject
     */
    protected $subject;

    /**
     * @var \Closure
     */
    protected $genericClosure;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->subject = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericClosure = function () {
            return;
        };

        $this->plugin = $objectManager->getObject(Rule::class);
    }

    public function testLoadRelations()
    {
        $this->assertEquals(
            $this->subject,
            $this->plugin->aroundLoadRelations($this->subject, $this->genericClosure)
        );
    }
}
