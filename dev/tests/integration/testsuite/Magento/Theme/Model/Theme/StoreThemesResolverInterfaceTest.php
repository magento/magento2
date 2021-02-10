<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use PHPUnit\Framework\TestCase;

class StoreThemesResolverInterfaceTest extends TestCase
{
    const XML_PATH_THEME_USER_AGENT = 'design/theme/ua_regexp';
    /**
     * @var StoreThemesResolverInterface
     */
    private $model;
    /**
     * @var Collection
     */
    private $themesCollection;
    /**
     * @var MutableScopeConfigInterface
     */
    private $mutableScopeConfig;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var string
     */
    private $userAgentDesignConfig;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->get(StoreThemesResolverInterface::class);
        $themesCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->themesCollection = $themesCollectionFactory->create();
        $this->mutableScopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        $this->serializer = $objectManager->get(Json::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->userAgentDesignConfig = $scopeConfig->getValue(
            self::XML_PATH_THEME_USER_AGENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->mutableScopeConfig->setValue(
            self::XML_PATH_THEME_USER_AGENT,
            $this->userAgentDesignConfig,
            ScopeInterface::SCOPE_STORE
        );
        parent::tearDown();
    }

    /**
     * @param array $config
     * @param array $expected
     * @dataProvider getThemesDataProvider
     */
    public function testGetThemes(array $config, array $expected): void
    {
        $store = $this->storeManager->getStore();
        $registeredThemes = [];
        foreach ($this->themesCollection as $theme) {
            $registeredThemes[$theme->getCode()] = $theme->getId();
        }
        // convert themes code to id
        foreach ($config as $key => $item) {
            $config[$key]['value'] = $registeredThemes[$item['value']];
        }
        $this->mutableScopeConfig->setValue(
            self::XML_PATH_THEME_USER_AGENT,
            $config ? $this->serializer->serialize($config) : null,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $expected = array_map(
            function ($theme) use ($registeredThemes) {
                return $registeredThemes[$theme];
            },
            $expected
        );
        $this->assertEquals(
            $expected,
            $this->model->getThemes($store),
            '',
            0.0,
            10,
            true
        );
    }

    /**
     * @return array
     */
    public function getThemesDataProvider(): array
    {
        return [
            [
                [
                ],
                [
                    'Magento/luma'
                ]
            ],
            [
                [
                    [
                        'search' => '\/Chrome\/i',
                        'regexp' => '\/Chrome\/i',
                        'value' => 'Magento/blank',
                    ]
                ],
                [
                    'Magento/luma',
                    'Magento/blank'
                ]
            ]
        ];
    }
}
