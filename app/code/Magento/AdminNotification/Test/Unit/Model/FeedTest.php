<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model;

use Magento\AdminNotification\Model\Feed;
use Magento\AdminNotification\Model\Inbox;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FeedTest extends TestCase
{
    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var InboxFactory|MockObject
     */
    protected $inboxFactory;

    /**
     * @var Inbox|MockObject
     */
    protected $inboxModel;

    /**
     * @var CurlFactory|MockObject
     */
    protected $curlFactory;

    /**
     * @var Curl|MockObject
     */
    protected $curl;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $backendConfig;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheManager;

    /**
     * @var State|MockObject
     */
    protected $appState;

    /**
     * @var DeploymentConfig|MockObject
     */
    protected $deploymentConfig;

    /**
     * @var ProductMetadata|MockObject
     */
    protected $productMetadata;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->inboxFactory = $this->createPartialMock(
            InboxFactory::class,
            ['create']
        );
        $this->curlFactory = $this->createPartialMock(CurlFactory::class, ['create']);
        $this->curl = $this->createMock(Curl::class);
        $this->appState = $this->createPartialMock(State::class, []);
        $this->inboxModel = $this->createPartialMock(Inbox::class, [
            '__wakeup',
            'parse'
        ]);
        $this->backendConfig = $this->createPartialMock(
            ConfigInterface::class,
            [
                'getValue',
                'setValue',
                'isSetFlag'
            ]
        );
        $this->cacheManager = $this->createPartialMock(
            CacheInterface::class,
            [
                'load',
                'getFrontend',
                'remove',
                'save',
                'clean'
            ]
        );

        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productMetadata = $this->createMock(ProductMetadata::class);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);

        $this->feed = $this->objectManagerHelper->getObject(
            Feed::class,
            [
                'backendConfig' => $this->backendConfig,
                'cacheManager' => $this->cacheManager,
                'inboxFactory' => $this->inboxFactory,
                'appState' => $this->appState,
                'curlFactory' => $this->curlFactory,
                'deploymentConfig' => $this->deploymentConfig,
                'productMetadata' => $this->productMetadata,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * @param bool $callInbox
     * @param string $curlRequest
     *
     * @return void
     * @dataProvider checkUpdateDataProvider
     */
    public function testCheckUpdate(bool $callInbox, string $curlRequest): void
    {
        $mockName    = 'Test Product Name';
        $mockVersion = '0.0.0';
        $mockEdition = 'Test Edition';
        $mockUrl = 'http://test-url';

        $this->productMetadata->expects($this->once())->method('getName')->willReturn($mockName);
        $this->productMetadata->expects($this->once())->method('getVersion')->willReturn($mockVersion);
        $this->productMetadata->expects($this->once())->method('getEdition')->willReturn($mockEdition);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with('*/*/*')->willReturn($mockUrl);

        $configValues = [
            'timeout'   => 2,
            'useragent' => $mockName . '/' . $mockVersion . ' (' . $mockEdition . ')',
            'referer'   => $mockUrl
        ];

        $lastUpdate = 0;
        $this->cacheManager->expects($this->once())->method('load')->willReturn($lastUpdate);
        $this->curlFactory
            ->method('create')
            ->willReturn($this->curl);
        $this->curl->expects($this->once())->method('setOptions')->with($configValues)->willReturnSelf();
        $this->curl->expects($this->once())->method('read')->willReturn($curlRequest);
        $this->backendConfig->expects($this->once())->method('isSetFlag')->willReturn(false);
        $this->backendConfig
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('1', 'http://feed.magento.com');
        $this->deploymentConfig->expects($this->once())->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE)
            ->willReturn('Sat, 6 Sep 2014 16:46:11 UTC');

        if ($callInbox) {
            $this->inboxFactory->expects($this->once())->method('create')
                ->willReturn($this->inboxModel);
            $this->inboxModel->expects($this->once())
                ->method('parse')
                ->with(
                    $this->callback(
                        function ($data) {
                            $fieldsToCheck = ['title', 'description', 'url'];
                            return array_reduce(
                                $fieldsToCheck,
                                function ($initialValue, $item) use ($data) {
                                    $haystack = $data[0][$item] ?? false;
                                    return $haystack
                                        ? $initialValue && !strpos($haystack, '<') && !strpos($haystack, '>')
                                        : true;
                                },
                                true
                            );
                        }
                    )
                )
                ->willReturnSelf();
        } else {
            $this->inboxFactory->expects($this->never())->method('create');
            $this->inboxModel->expects($this->never())->method('parse');
        }

        $this->feed->checkUpdate();
    }

    /**
     * @return array
     */
    public function checkUpdateDataProvider(): array
    {
        return [
            [
                true,
                'HEADER

                <?xml version="1.0" encoding="utf-8" ?>
                        <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
                            <channel>
                                <title>MagentoCommerce</title>
                                <item>
                                    <title><![CDATA[Test Title]]></title>
                                    <link><![CDATA[http://magento.com/feed_url]]></link>
                                    <severity>4</severity>
                                    <description><![CDATA[Test Description]]></description>
                                    <pubDate>Tue, 9 Sep 2014 16:46:11 UTC</pubDate>
                                </item>
                            </channel>
                        </rss>',
            ],
            [
                false,
                'HEADER

                <?xml version="1.0" encoding="utf-8" ?>
                        <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
                            <channel>
                                <title>MagentoCommerce</title>
                                <item>
                                    <title><![CDATA[Test Title]]></title>
                                    <link><![CDATA[http://magento.com/feed_url]]></link>
                                    <severity>4</severity>
                                    <description><![CDATA[Test Description]]></description>
                                    <pubDate>Tue, 1 Sep 2014 16:46:11 UTC</pubDate>
                                </item>
                            </channel>
                        </rss>'
            ],
            [
                true,
                // @codingStandardsIgnoreStart
                'HEADER

                <?xml version="1.0" encoding="utf-8" ?>
                        <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
                            <channel>
                                <title>MagentoCommerce</title>
                                <item>
                                    <title><![CDATA[<script>alert("Hello!");</script>Test Title]]></title>
                                    <link><![CDATA[http://magento.com/feed_url<script>alert("Hello!");</script>]]></link>
                                    <severity>4</severity>
                                    <description><![CDATA[Test <script>alert("Hello!");</script>Description]]></description>
                                    <pubDate>Tue, 20 Jun 2017 13:14:47 UTC</pubDate>
                                </item>
                            </channel>
                        </rss>'
                // @codingStandardsIgnoreEnd
            ]
        ];
    }
}
