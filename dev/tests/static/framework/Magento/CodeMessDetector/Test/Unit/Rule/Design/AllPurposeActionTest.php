<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CodeMessDetector\Test\Unit\Rule\Design;

use Magento\CodeMessDetector\Rule\Design\AllPurposeAction;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ActionInterface;
use PHPUnit\Framework\TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMocker;
use PHPMD\Report;
use PHPMD\Node\ClassNode;

class AllPurposeActionTest extends TestCase
{
    /**
     * @param object $fakeAction
     * @param bool $violates
     *
     * @dataProvider getCases
     */
    public function testApply($fakeAction, bool $violates)
    {
        $node = $this->createNodeMock($fakeAction);
        $rule = new AllPurposeAction();
        $this->expectsRuleViolation($rule, $violates);
        $rule->apply($node);
    }

    /**
     * @return array
     */
    public function getCases(): array
    {
        return [
            [
                new class implements ActionInterface, HttpGetActionInterface {
                    /**
                     * @inheritDoc
                     */
                    public function execute()
                    {
                        return null;
                    }
                },
                false
            ],
            [
                new class implements ActionInterface {
                    /**
                     * @inheritDoc
                     */
                    public function execute()
                    {
                        return null;
                    }
                },
                true
            ],
            [
                new class implements HttpGetActionInterface {
                    /**
                     * @inheritDoc
                     */
                    public function execute()
                    {
                        return null;
                    }
                },
                false
            ],
            [
                new class {

                },
                false
            ]
        ];
    }

    /**
     * @param object $dynamicObject
     *
     * @return ClassNode|MockObject
     */
    private function createNodeMock($dynamicObject): MockObject
    {
        $node = $this->getMockBuilder(ClassNode::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods([
                // disable name lookup from AST artifact
                'getNamespaceName',
                'getParentName',
                'getName',
                'getFullQualifiedName',
            ])
            ->getMock();
        $node->expects($this->any())
            ->method('getFullQualifiedName')
            ->willReturn(get_class($dynamicObject));

        return $node;
    }

    /**
     * @param AllPurposeAction $rule
     * @param bool $expects
     * @return InvocationMocker
     */
    private function expectsRuleViolation(
        AllPurposeAction $rule,
        bool $expects
    ): InvocationMocker {
        /** @var Report|MockObject $report */
        $report = $this->getMockBuilder(Report::class)->getMock();
        if ($expects) {
            $violationExpectation = $this->atLeastOnce();
        } else {
            $violationExpectation = $this->never();
        }
        $invokation = $report->expects($violationExpectation)
            ->method('addRuleViolation');
        $rule->setReport($report);

        return $invokation;
    }
}
