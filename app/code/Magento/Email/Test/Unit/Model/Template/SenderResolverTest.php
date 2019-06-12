<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\SenderResolver;
use Magento\Framework\Exception\MailException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 *  SenderResolverTest
 */
class SenderResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SenderResolver
     */
    private $senderResolver;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @return void
     */
<<<<<<< HEAD
    public function setUp()
=======
    public function setUp(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

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
<<<<<<< HEAD
    public function testResolve()
=======
    public function testResolve(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $sender = 'general';
        $scopeId = null;

        $this->scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
               [
                   'trans_email/ident_' . $sender . '/name',
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                   $scopeId,
                   'Test Name'
               ],
               [
                   'trans_email/ident_' . $sender . '/email',
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                   $scopeId,
                   'test@email.com'
               ]
            ]);

        $result = $this->senderResolver->resolve($sender);

        $this->assertTrue(isset($result['name']));
        $this->assertEquals('Test Name', $result['name']);

        $this->assertTrue(isset($result['email']));
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
<<<<<<< HEAD
    public function testResolveThrowException(array $sender)
=======
    public function testResolveThrowException(array $sender): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
