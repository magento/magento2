<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Ui\Component\Listing\Column\Store;

use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Store\Ui\Component\Listing\Column\Store\Options.
 */
class OptionsTest extends TestCase
{
    private const DEFAULT_WEBSITE_NAME = 'Main Website';
    private const DEFAULT_STORE_GROUP_NAME = 'Main Website Store';
    private const DEFAULT_STORE_NAME = 'Default Store View';

    /**
     * @var OptionsFactory
     */
    private $modelFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WebsiteResource
     */
    private $websiteResource;

    /**
     * @var StoreResource
     */
    private $storeResource;

    /**
     * @var GroupResource
     */
    private $groupResource;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->modelFactory = $objectManager->get(OptionsFactory::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);

        $this->websiteResource = $objectManager->get(WebsiteResource::class);
        $this->groupResource = $objectManager->get(GroupResource::class);
        $this->storeResource = $objectManager->get(StoreResource::class);
    }

    /**
     * To option array test with duplicate website, store group, store view names
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     *
     * @return void
     */
    public function testToOptionArray(): void
    {
        $website = $this->storeManager->getWebsite('test');
        $this->websiteResource->save($website->setName(self::DEFAULT_WEBSITE_NAME));

        $storeGroup = current($website->getGroups());
        $this->groupResource->save($storeGroup->setName(self::DEFAULT_STORE_GROUP_NAME));

        $store = current($website->getStores());
        $this->storeResource->save($store->setName(self::DEFAULT_STORE_NAME));

        $model = $this->modelFactory->create();
        $storeIds = [$this->storeManager->getStore('default')->getId(), $store->getId()];

        $this->assertEquals($this->getExpectedOptions($storeIds), $model->toOptionArray());
    }

    /**
     * Returns expected options
     *
     * @param array $storeIds
     * @return array
     */
    private function getExpectedOptions(array $storeIds): array
    {
        $expectedOptions = [];
        foreach ($storeIds as $storeId) {
            $expectedOptions[] = [
                'label' => self::DEFAULT_WEBSITE_NAME,
                'value' => [[
                    'label' => str_repeat(' ', 4) . self::DEFAULT_STORE_GROUP_NAME,
                    'value' => [[
                        'label' => str_repeat(' ', 8) . self::DEFAULT_STORE_NAME,
                        'value' => $storeId,
                    ]],
                ]],
            ];
        }

        return $expectedOptions;
    }
}
