<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Vat;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class VatTest extends TestCase
{
    public function testGetVatValidationGroupIdsSkipsEmptyAndRepeatedGroups(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap(
            [
                [Vat::XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP, ScopeInterface::SCOPE_STORE, 3, '1'],
                [Vat::XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP, ScopeInterface::SCOPE_STORE, 3, '4'],
                [Vat::XML_PATH_CUSTOMER_VIV_INVALID_GROUP, ScopeInterface::SCOPE_STORE, 3, '1'],
                [Vat::XML_PATH_CUSTOMER_VIV_ERROR_GROUP, ScopeInterface::SCOPE_STORE, 3, ''],
            ]
        );
        $vat = new Vat($scopeConfig, $this->createMock(LoggerInterface::class));

        $this->assertSame([1, 4], $vat->getVatValidationGroupIds(3));
    }
}
