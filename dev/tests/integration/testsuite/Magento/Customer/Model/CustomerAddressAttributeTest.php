<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Model\Metadata\AddressMetadata;
use Magento\Eav\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CustomerAddressAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->get(Config::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Tests cached scope_is_required attribute value for a certain website
     *
     * @return void
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testGetScopeIsRequiredAttributeValueFromCache(): void
    {
        $attributeCode = 'telephone';
        $entityType = AddressMetadata::ENTITY_TYPE_ADDRESS;
        $attribute = $this->config->getAttribute($entityType, $attributeCode);
        $currentStore = $this->storeManager->getStore();
        $secondWebsite = $this->storeManager->getWebsite('test');
        $attribute->setWebsite($secondWebsite->getId());
        $attribute->setData('scope_is_required', '0');
        $attribute->save();
        $this->config->getAttribute($entityType, $attributeCode);
        $this->storeManager->setCurrentStore('fixture_second_store');
        try {
            $this->config->getEntityAttributes($attribute->getEntityTypeId(), $attribute);
            $scopeAttribute = $this->config->getAttribute($entityType, $attributeCode);
            $this->assertEquals(0, $scopeAttribute->getIsRequired());
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }
}
