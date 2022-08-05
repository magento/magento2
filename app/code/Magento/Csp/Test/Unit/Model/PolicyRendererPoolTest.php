<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\Renderer\SimplePolicyHeaderRenderer;
use Magento\Csp\Model\PolicyRendererPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Test for \Magento\Csp\Model\PolicyRendererPool
 */
class PolicyRendererPoolTest extends TestCase
{
    private const STUB_POLICY_ID = 'header';

    /**
     * @var PolicyRendererPool
     */
    private $model;

    /**
     * @var SimplePolicyHeaderRenderer|MockObject
     */
    private $simplePolicyHeaderRendererMock;

    /**
     * @var PolicyInterface|MockObject
     */
    private $policyMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->simplePolicyHeaderRendererMock = $this->createPartialMock(
            SimplePolicyHeaderRenderer::class,
            ['canRender']
        );
        $this->policyMock = $this->getMockForAbstractClass(PolicyInterface::class);

        $this->model = $objectManager->getObject(
            PolicyRendererPool::class,
            [
                'renderers' => [
                    $this->simplePolicyHeaderRendererMock
                ]
            ]
        );
    }

    /**
     * Test throwing an exception for not found policy renders
     *
     * @return void
     */
    public function testThrownExceptionForNotFoundPolicyRenders()
    {
        $this->policyMock->expects($this->any())
            ->method('getId')
            ->willReturn(static::STUB_POLICY_ID);

        $this->expectExceptionMessage('Failed to find a renderer for policy');
        $this->expectException(RuntimeException::class);

        $this->model->getRenderer($this->policyMock);
    }

    /**
     * Test returning a renderer for the given policy
     *
     * @return void
     */
    public function testReturningThePolicyRender()
    {
        $this->simplePolicyHeaderRendererMock->expects($this->any())
            ->method('canRender')
            ->with($this->policyMock)
            ->willReturn(true);

        $this->model->getRenderer($this->policyMock);
    }
}
