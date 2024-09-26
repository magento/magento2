<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Model\Customer\Authorization as PersistentAuthorization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\Customer\AuthorizationComposite as CustomerAuthorizationComposite;

/**
 * A test class for the persistent customers authorization
 *
 * Unit tests for \Magento\Persistent\Model\Customer\Authorization class.
 */
class AuthorizationTest extends TestCase
{
    /**
     * @var PersistentSession|MockObject
     */
    private $persistentSessionMock;

    /**
     * @var PersistentAuthorization
     */
    private $persistentCustomerAuthorization;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var CustomerAuthorizationComposite
     */
    private $customerAuthorizationComposite;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->persistentSessionMock = $this->getMockBuilder(PersistentSession::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsCustomerEmulated'])
            ->onlyMethods(['getCustomerId'])
            ->getMock();

        $this->persistentCustomerAuthorization = new PersistentAuthorization(
            $this->customerSessionMock,
            $this->persistentSessionMock
        );

        $this->customerAuthorizationComposite = new CustomerAuthorizationComposite(
            [$this->persistentCustomerAuthorization]
        );
    }

    /**
     * Validate if isAuthorized() will return proper permission value for logged in/ out persistent customers
     *
     * @dataProvider persistentLoggedInCombinations
     * @param bool $isPersistent
     * @param int|null $customerId
     * @param bool|null $isCustomerEmulated
     * @param bool $shouldBeAllowed
     */
    public function testIsAuthorized(
        bool $isPersistent,
        ?int $customerId,
        ?bool $isCustomerEmulated,
        bool $shouldBeAllowed
    ): void {
        $this->persistentSessionMock->expects($this->any())->method('isPersistent')->willReturn($isPersistent);
        $this->customerSessionMock->expects($this->any())->method('getCustomerId')->willReturn($customerId);
        $this->customerSessionMock->expects($this->any())->method('getIsCustomerEmulated')->willReturn($isCustomerEmulated);

        $isAllowedResult = $this->customerAuthorizationComposite->isAllowed('self');

        $this->assertEquals($shouldBeAllowed, $isAllowedResult);
    }

    /**
     * @return array
     */
    public static function persistentLoggedInCombinations(): array
    {
        return [
            'Emulated persistent Customer ID#1 should not be authorized' => [
                'isPersistent' => true,
                'customerId' => 1,
                'isCustomerEmulated' => true,
                'shouldBeAllowed' => false
            ],
            'Logged-in persistent Customer ID#1 should be authorized' => [
                'isPersistent' => true,
                'customerId' => 1,
                'isCustomerEmulated' => false,
                'shouldBeAllowed' => true
            ],
            'Logged-in Customer ID#1 without persistency should be authorized' => [
                'isPersistent' => false,
                'customerId' => 1,
                'isCustomerEmulated' => false,
                'shouldBeAllowed' => true
            ],
            'Persistent Customer ID/ isCustomerEmulated = null (API Request) should be authorized' => [
                'isPersistent' => true,
                'customerId' => null,
                'isCustomerEmulated' => null,
                'shouldBeAllowed' => true
            ]
        ];
    }
}
