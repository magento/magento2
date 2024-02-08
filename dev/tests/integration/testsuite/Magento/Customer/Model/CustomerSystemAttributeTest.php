<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CustomerSystemAttributeTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Verifies that customer system attributes are cached per website.
     *
     * @return void
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testCustomerSystemAttributePerWebsite(): void
    {
        $systemAttributeCode = 'taxvat';
        $entityType = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
        $currentStore = $this->storeManager->getStore();
        $config = Bootstrap::getObjectManager()->get(Config::class);

        $attribute = $config->getAttribute($entityType, $systemAttributeCode);
        $attribute->setData('scope_is_visible', '0');
        $attribute->save();

        $secondWebsite = $this->storeManager->getWebsite('test');
        $attribute->setWebsite($secondWebsite->getId());
        $attribute->setData('scope_is_visible', '1');
        $attribute->save();

        $this->storeManager->setCurrentStore('fixture_second_store');
        $config = Bootstrap::getObjectManager()->create(Config::class);
        $scopeAttribute = $config->getAttribute($entityType, $systemAttributeCode);
        $this->assertEquals(
            1,
            $scopeAttribute->getData('scope_is_visible'),
            'Attribute visibility doesn\'t correspond the scope'
        );

        $this->storeManager->setCurrentStore($currentStore);
        $config = Bootstrap::getObjectManager()->create(Config::class);
        $scopeAttribute = $config->getAttribute($entityType, $systemAttributeCode);
        $this->assertEquals(
            0,
            $scopeAttribute->getData('scope_is_visible'),
            'Attribute visibility doesn\'t correspond the scope'
        );
    }
}
