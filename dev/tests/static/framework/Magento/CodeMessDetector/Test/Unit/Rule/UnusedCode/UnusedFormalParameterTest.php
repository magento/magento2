<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CodeMessDetector\Test\Unit\Rule\UnusedCode;

use Magento\CodeMessDetector\Rule\UnusedCode\UnusedFormalParameter;
use PHPMD\Node\ASTNode;
use PHPMD\Node\MethodNode;
use PHPMD\Report;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UnusedFormalParameterTest extends TestCase
{
    private const FAKE_PLUGIN_NAMESPACE = 'Magento\CodeMessDetector\Test\UnusedCode\Plugin';
    private const FAKE_NAMESPACE = 'Magento\CodeMessDetector\Test\UnusedCode';

    /**
     *
     * @dataProvider getCases
     */
    public function testApply($methodName, $methodParams, $namespace, $expectViolation)
    {
        $node = $this->createMethodNodeMock($methodName, $methodParams, $namespace);
        $rule = new UnusedFormalParameter();
        $this->expectsRuleViolation($rule, $expectViolation);
        $rule->apply($node);
    }

    /**
     * Prepare method node mock
     *
     * @param $methodName
     * @param $methodParams
     * @param $namespace
     * @return MethodNode|MockObject
     */
    private function createMethodNodeMock($methodName, $methodParams, $namespace)
    {
        $methodNode = $this->createConfiguredMock(
            MethodNode::class,
            [
                'getName' => $methodName,
                'getImage' => $methodName,
                'isAbstract' => false,
                'isDeclaration' => true
            ]
        );

        $variableDeclarators = [];
        foreach ($methodParams as $methodParam) {
            $variableDeclarator = $this->createASTNodeMock();
            $variableDeclarator->method('getImage')
                ->willReturn($methodParam);

            $variableDeclarators[] = $variableDeclarator;
        }
        $parametersMock = $this->createASTNodeMock();
        $parametersMock->expects($this->once())
            ->method('findChildrenOfType')
            ->with('VariableDeclarator')
            ->willReturn($variableDeclarators);

        /**
         * Declare mock result for findChildrenOfType
         * with Dummy for removeCompoundVariables and removeVariablesUsedByFuncGetArgs
         */
        $methodNode->expects($this->atLeastOnce())
            ->method('findChildrenOfType')
            ->withConsecutive(['FormalParameters'], ['CompoundVariable'], ['FunctionPostfix'])
            ->willReturnOnConsecutiveCalls([$parametersMock], [], []);

        // Dummy result for removeRegularVariables
        $methodNode->expects($this->once())
            ->method('findChildrenOfTypeVariable')
            ->willReturn([]);

        $classNode = $this->createASTNodeMock();
        $classNode->expects($this->once())
            ->method('getNamespaceName')
            ->willReturn($namespace);
        $methodNode->expects($this->once())
            ->method('getParentType')
            ->willReturn($classNode);

        return $methodNode;
    }

    /**
     * Create ASTNode mock
     *
     * @return ASTNode|MockObject
     */
    private function createASTNodeMock()
    {
        return $this->createMock(ASTNode::class);
    }

    /**
     * @param UnusedFormalParameter $rule
     * @param bool $expects
     */
    private function expectsRuleViolation(UnusedFormalParameter $rule, bool $expects)
    {
        /** @var Report|MockObject $reportMock */
        $reportMock = $this->createMock(Report::class);
        if ($expects) {
            $violationExpectation = $this->atLeastOnce();
        } else {
            $violationExpectation = $this->never();
        }
        $reportMock->expects($violationExpectation)
            ->method('addRuleViolation');
        $rule->setReport($reportMock);
    }

    /**
     * @return array
     */
    public function getCases(): array
    {
        return [
            // Plugin methods
            [
                'beforePluginMethod',
                [
                    'subject'
                ],
                self::FAKE_PLUGIN_NAMESPACE,
                false
            ],
            [
                'aroundPluginMethod',
                [
                    'subject',
                    'proceed'
                ],
                self::FAKE_PLUGIN_NAMESPACE,
                false
            ],
            [
                'aroundPluginMethod',
                [
                    'subject',
                    'result'
                ],
                self::FAKE_PLUGIN_NAMESPACE,
                false
            ],
            // Plugin method that contain unused parameter
            [
                'someMethod',
                [
                    'unusedParameter'
                ],
                self::FAKE_PLUGIN_NAMESPACE,
                true
            ],
            // Non plugin method
            [
                'someMethod',
                [
                    'subject',
                    'result'
                ],
                self::FAKE_NAMESPACE,
                true
            ]
        ];
    }
}
