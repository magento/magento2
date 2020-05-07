<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\SenderResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SenderResolverTest extends TestCase
{
    /**
     * @var SenderResolver
     */
    private $senderResolver;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->senderResolver = $objectManager->getObject(
            SenderResolver::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    /**
     * Test returned information for given sender's name and email
     *
     * @return void
     */
    public function testResolve(): void
    {
        $sender = 'general';
        $scopeId = null;

        $this->scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [
                    'trans_email/ident_' . $sender . '/name',
                    ScopeInterface::SCOPE_STORE,
                    $scopeId,
                    'Test Name'
                ],
                [
                    'trans_email/ident_' . $sender . '/email',
                    ScopeInterface::SCOPE_STORE,
                    $scopeId,
                    'test@email.com'
                ]
            ]);

        $result = $this->senderResolver->resolve($sender);

        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('Test Name', $result['name']);

        $this->assertArrayHasKey('email', $result);
        $this->assertEquals('test@email.com', $result['email']);
    }

    /**
     * Test if exception is thrown in case there is no name or email in result
     *
     * @dataProvider dataProvidedSenderArray
     * @param array $sender
     *
     * @return void
     */
    public function testResolveThrowException(array $sender): void
    {
        $this->expectExceptionMessage('Invalid sender data');
        $this->expectException(MailException::class);
        $this->senderResolver->resolve($sender);
    }

    /**
     * @return array
     */
    public function dataProvidedSenderArray()
    {
        return [
            [
                ['name' => 'Name']
            ],
            [
                ['email' => 'test@email.com']
            ]
        ];
    }
}
