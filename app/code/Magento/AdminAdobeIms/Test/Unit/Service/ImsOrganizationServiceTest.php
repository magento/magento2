<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdminAdobeIms\Service\ImsOrganizationService;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class ImsOrganizationServiceTest extends TestCase
{
    private const VALID_ORGANIZATION_ID = '12121212ABCD1211AA11ABCD';
    private const INVALID_ORGANIZATION_ID = '12121212ABCD1211AA11XXXX';

    /**
     * @var ImsOrganizationService
     */
    private $imsOrganizationService;

    /**
     * @var ImsConfig
     */
    private $imsConfigMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->imsConfigMock = $this->createMock(ImsConfig::class);

        $this->imsOrganizationService = $objectManagerHelper->getObject(
            ImsOrganizationService::class,
            [
                'imsConfig' => $this->imsConfigMock
            ]
        );
    }

    public function testCheckOrganizationAllocationWithEmptyProfileRolesThrowsException()
    {
        $this->expectException(AdobeImsOrganizationAuthorizationException::class);
        $this->expectExceptionMessage('No roles assigned for profile');
        $this->imsOrganizationService->checkOrganizationAllocation([]);
    }

    public function testCheckOrganizationAllocationReturnsTrueWhenProfileAssignedToOrg()
    {
        $this->imsConfigMock
            ->method('getOrganizationId')
            ->willReturn(self::VALID_ORGANIZATION_ID);

        $this->assertEquals(
            true,
            $this->imsOrganizationService->checkOrganizationAllocation([
                'roles' => [
                    ['organization' => '12121212ABCD1211AA11ABCD', 'named_role' => 'test']
                ]
            ])
        );
    }

    public function testCheckOrganizationAllocationThrowsExceptionWhenProfileNotAssignedToOrg()
    {
        $this->imsConfigMock
            ->method('getOrganizationId')
            ->willReturn(self::INVALID_ORGANIZATION_ID);

        $this->expectException(AdobeImsOrganizationAuthorizationException::class);
        $this->expectExceptionMessage('Profile is not assigned to defined organization.');

        $this->imsOrganizationService->checkOrganizationAllocation([
            'roles' => [
                ['organization' => '12121212ABCD1211AA11ABCD', 'named_role' => 'test']
            ]
        ]);
    }
}
