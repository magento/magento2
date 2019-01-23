<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CodeMessDetector\Test\Unit\Rule\Design;

use PHPUnit\Framework\TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder as InvokedRecorder;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMocker;
use Magento\CodeMessDetector\Rule\Design\FinalImplementation;
use PHPMD\Report;
use PHPMD\AbstractNode;
use PHPMD\Node\ClassNode;
use PHPMD\Node\MethodNode;
use BadMethodCallException;

class FinalImplementationTest extends TestCase
{
    /**
     * @param string $nodeType
     *
     * @dataProvider finalizableNodeTypesProvider
     */
    public function testRuleNotAppliesToNotFinalFinalizable($nodeType)
    {
        $finalizableNode = $this->createFinalizableNodeMock($nodeType);
        $finalizableNode->method('isFinal')->willReturn(false);

        $rule = new FinalImplementation();
        $this->expectsRuleViolation($rule, $this->never());
        $rule->apply($finalizableNode);
    }

    /**
     * @param string $nodeType
     *
     * @dataProvider finalizableNodeTypesProvider
     */
    public function testRuleAppliesToFinalFinalizable($nodeType)
    {
        $finalizableNode = $this->createFinalizableNodeMock($nodeType);
        $finalizableNode->method('isFinal')->willReturn(true);

        $rule = new FinalImplementation();
        $this->expectsRuleViolation($rule, $this->once());
        $rule->apply($finalizableNode);
    }

    /**
     * @param string $nodeType
     *
     * @dataProvider finalizableNodeTypesProvider
     */
    public function testRuleVerifiesFinalizableNodes($nodeType)
    {
        $finalizableNode = $this->createFinalizableNodeMock($nodeType);

        $finalizableNode->expects($this->atLeastOnce())
            ->method('isFinal');

        $rule = new FinalImplementation();
        $rule->apply($finalizableNode);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testRuleFailsOnNotFinalizableNodes()
    {
        $someNode = $this->getMockBuilder(AbstractNode::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $rule = new FinalImplementation();
        $rule->apply($someNode);
    }

    /**
     * "final" keyword may be applied only to classes and methods
     *
     * @return array
     */
    public function finalizableNodeTypesProvider()
    {
        return [
            [ClassNode::class],
            [MethodNode::class],
        ];
    }

    /**
     * If node is finalizable it has "isFinal" magic PHP method
     *
     * @param string $nodeType
     * @return ClassNode|MethodNode|MockObject
     */
    private function createFinalizableNodeMock($nodeType)
    {
        $finalizableNode = $this->getMockBuilder($nodeType)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods([
                'isFinal',
                // disable name lookup from AST artifact
                'getNamespaceName',
                'getParentName',
                'getName',
            ])
            ->getMock();
        return $finalizableNode;
    }

    /**
     * @param FinalImplementation $rule
     * @param InvokedRecorder $violationExpectation
     * @return InvocationMocker
     */
    private function expectsRuleViolation(FinalImplementation $rule, InvokedRecorder $violationExpectation)
    {
        $report = $this->getMockBuilder(Report::class)->getMock();
        $invokation = $report->expects($violationExpectation)->method('addRuleViolation');
        $rule->setReport($report);
        return $invokation;
    }
}
