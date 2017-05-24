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
class DefaultFrontendTest extends \PHPUnit_Framework_TestCase
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
