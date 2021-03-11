<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Test\Unit\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FeedTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\AdminNotification\Model\Feed */
    protected $feed;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\AdminNotification\Model\InboxFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $inboxFactory;

    /** @var \Magento\AdminNotification\Model\Inbox|\PHPUnit\Framework\MockObject\MockObject */
    protected $inboxModel;

    /** @var \Magento\Framework\HTTP\Adapter\CurlFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $curlFactory;

    /** @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit\Framework\MockObject\MockObject */
    protected $curl;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $backendConfig;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $cacheManager;

    /** @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject */
    protected $appState;

    /** @var \Magento\Framework\App\DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject */
    protected $deploymentConfig;

    /** @var \Magento\Framework\App\ProductMetadata|\PHPUnit\Framework\MockObject\MockObject */
    protected $productMetadata;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlBuilder;

    protected function setUp(): void
    {
        $this->inboxFactory = $this->createPartialMock(
            \Magento\AdminNotification\Model\InboxFactory::class,
            ['create']
        );
        $this->curlFactory = $this->createPartialMock(\Magento\Framework\HTTP\Adapter\CurlFactory::class, ['create']);
        $this->curl = $this->getMockBuilder(\Magento\Framework\HTTP\Adapter\Curl::class)
            ->disableOriginalConstructor()->getMock();
        $this->appState = $this->createPartialMock(\Magento\Framework\App\State::class, ['getInstallDate']);
        $this->inboxModel = $this->createPartialMock(\Magento\AdminNotification\Model\Inbox::class, [
                '__wakeup',
                'parse'
            ]);
        $this->backendConfig = $this->createPartialMock(
            \Magento\Backend\App\ConfigInterface::class,
            [
                'getValue',
                'setValue',
                'isSetFlag'
            ]
        );
        $this->cacheManager = $this->createPartialMock(
            \Magento\Framework\App\CacheInterface::class,
            [
                'load',
                'getFrontend',
                'remove',
                'save',
                'clean'
            ]
        );

        $this->deploymentConfig = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->productMetadata = $this->getMockBuilder(\Magento\Framework\App\ProductMetadata::class)
            ->disableOriginalConstructor()->getMock();

        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);

        $this->feed = $this->objectManagerHelper->getObject(
            \Magento\AdminNotification\Model\Feed::class,
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
     * @dataProvider checkUpdateDataProvider
     * @param bool $callInbox
     * @param string $curlRequest
     */
    public function testCheckUpdate($callInbox, $curlRequest)
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
        $this->cacheManager->expects($this->once())->method('load')->will(($this->returnValue($lastUpdate)));
        $this->curlFactory->expects($this->at(0))->method('create')->willReturn($this->curl);
        $this->curl->expects($this->once())->method('setConfig')->with($configValues)->willReturnSelf();
        $this->curl->expects($this->once())->method('read')->willReturn($curlRequest);
        $this->backendConfig->expects($this->at(0))->method('getValue')->willReturn('1');
        $this->backendConfig->expects($this->once())->method('isSetFlag')->willReturn(false);
        $this->backendConfig->expects($this->at(1))->method('getValue')
            ->willReturn('http://feed.magento.com');
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
    public function checkUpdateDataProvider()
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
            ],
        ];
    }
}
