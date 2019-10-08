<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Adminhtml\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     *
     * @dataProvider saveMerchantCountryDataProvider
     *
     * @param string $section
     * @param array $groups
     * @return void
     */
    public function testSaveMerchantCountry(string $section, array $groups): void
    {
        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig = Bootstrap::getObjectManager()->get(ScopeConfigInterface::class);

        $request = $this->getRequest();
        $request->setPostValue($groups)
            ->setParam('section', $section)
            ->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/admin/system_config/save');

        $this->assertSessionMessages($this->equalTo(['You saved the configuration.']));

        $this->assertEquals(
            'GB',
            $scopeConfig->getValue('paypal/general/merchant_country')
        );
    }

    /**
     * @return array
     */
    public function saveMerchantCountryDataProvider(): array
    {
        return [
            [
                'section' => 'paypal',
                'groups' => [
                    'groups' => [
                        'general' => [
                            'fields' => [
                                'merchant_country' => ['value' => 'GB'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'section' => 'payment',
                'groups' => [
                    'groups' => [
                        'account' => [
                            'fields' => [
                                'merchant_country' => ['value' => 'GB'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
