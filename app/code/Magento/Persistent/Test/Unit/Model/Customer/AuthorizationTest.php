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
            ->onlyMethods(['isPersistent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->onlyMethods(['isLoggedIn'])
            ->disableOriginalConstructor()
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
     * @param bool $isLoggedIn
     * @param bool $isAllowedExpectation
     */
    public function testIsAuthorized(
        bool $isPersistent,
        bool $isLoggedIn,
        bool $isAllowedExpectation
    ): void {
        $this->persistentSessionMock->method('isPersistent')->willReturn($isPersistent);
        $this->customerSessionMock->method('isLoggedIn')->willReturn($isLoggedIn);
        $isAllowedResult = $this->customerAuthorizationComposite->isAllowed('self');

        $this->assertEquals($isAllowedExpectation, $isAllowedResult);
    }

    /**
     * @return array
     */
    public function persistentLoggedInCombinations(): array
    {
        return [
            [
                true,
                false,
                false
            ],
            [
                true,
                true,
                true
            ],
            [
                false,
                false,
                true
            ],
        ];
    }
}
