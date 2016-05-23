<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Adminhtml\Source;

use Magento\Payment\Model\MethodInterface;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;

class VaultProvidersMapTest extends \PHPUnit_Framework_TestCase
{
    public function testToOptionArrayDefault()
    {
        $sourceModel = new VaultProvidersMap();

        static::assertEmpty(
            $sourceModel->toOptionArray()
        );
    }

    public function testToOptionArray()
    {
        $vaultPayment1 = $this->getMock(MethodInterface::class);
        $vaultPayment2 = $this->getMock(MethodInterface::class);

        $vaultPayment1->expects(static::exactly(2))
            ->method('getCode')
            ->willReturn('vault_payment1');

        $vaultPayment2->expects(static::exactly(2))
            ->method('getCode')
            ->willReturn('vault_payment2');

        $sourceModel = new VaultProvidersMap(
            [
                $vaultPayment1,
                $vaultPayment2
            ]
        );

        static::assertEquals(
            [
                [
                    'value' => VaultProvidersMap::EMPTY_VALUE,
                    'label' => __('Select vault provider')
                ],
                [
                    'value' => 'vault_payment1',
                    'label' => __('vault_payment1')
                ],
                [
                    'value' => 'vault_payment2',
                    'label' => __('vault_payment2')
                ]
            ],
            $sourceModel->toOptionArray()
        );
    }
}
