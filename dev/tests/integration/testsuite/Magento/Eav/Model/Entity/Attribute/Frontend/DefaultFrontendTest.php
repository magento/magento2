<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Frontend;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Eav\Model\Entity\Attribute;

/**
 * @magentoAppIsolation enabled
 */
class DefaultFrontendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultFrontend
     */
    private $defaultFrontend;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AbstractAttribute
     */
    private $attribute;

    /**
     * @var array
     */
    private $options;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp()
    {
        CacheCleaner::cleanAll();
        $this->objectManager = Bootstrap::getObjectManager();

        $this->defaultFrontend = $this->objectManager->get(DefaultFrontend::class);
        $this->cache = $this->objectManager->get(CacheInterface::class);
        $this->storeResolver = $this->objectManager->get(StoreResolverInterface::class);
        $this->serializer = $this->objectManager->get(Serializer::class);
        $this->attribute = $this->objectManager->get(Attribute::class);

        $this->attribute->setAttributeCode('store_id');
        $this->options = $this->attribute->getSource()->getAllOptions();
        $this->defaultFrontend->setAttribute($this->attribute);
    }

    public function testGetSelectOptions()
    {
        $this->assertSame($this->options, $this->defaultFrontend->getSelectOptions());
        $this->assertSame(
            $this->serializer->serialize($this->options),
            $this->cache->load($this->getCacheKey())
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @return void
     */
    public function testAttributeEntityValueNotSet()
    {
        $entity = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $entity->setStoreId(0);
        $entity->load(1);
        $frontEnd = $this->attribute->loadByCode('catalog_product', 'dropdown_attribute');
        $value = $frontEnd->getFrontend()->getValue($entity);

        $this->assertFalse($value);
    }

    /**
     * Cache key generation
     * @return string
     */
    private function getCacheKey()
    {
        return 'attribute-navigation-option-' .
            $this->defaultFrontend->getAttribute()->getAttributeCode() . '-' .
            $this->storeResolver->getCurrentStoreId();
    }
}
