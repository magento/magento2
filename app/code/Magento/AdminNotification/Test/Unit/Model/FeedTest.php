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
use PHPUnit\Framework\TestCase;

/**
 * Class FeedTest
 *
 * @package Magento\AdminNotification\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FeedTest extends TestCase
{
    /** @var Feed */
    protected $feed;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var InboxFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $inboxFactory;

    /** @var Inbox|\PHPUnit_Framework_MockObject_MockObject */
    protected $inboxModel;

    /** @var CurlFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $curlFactory;

    /** @var Curl|\PHPUnit_Framework_MockObject_MockObject */
    protected $curl;

    /** @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendConfig;

    /** @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager;

    /** @var State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    /** @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $deploymentConfig;

    /** @var ProductMetadata|\PHPUnit_Framework_MockObject_MockObject */
    protected $productMetadata;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    protected function setUp()
    {
        $this->inboxFactory = $this->createPartialMock(
            InboxFactory::class,
            ['create']
        );
        $this->curlFactory = $this->createPartialMock(CurlFactory::class, ['create']);
        $this->curl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()->getMock();
        $this->appState = $this->createPartialMock(State::class, ['getInstallDate']);
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

        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->productMetadata = $this->getMockBuilder(ProductMetadata::class)
            ->disableOriginalConstructor()->getMock();

        $this->urlBuilder = $this->createMock(UrlInterface::class);

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
     * @dataProvider checkUpdateDataProvider
     * @param bool $callInbox
     * @param string $curlRequest
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testCheckUpdate($callInbox, $curlRequest)
    {
        $mockName    = 'Test Product Name';
        $mockVersion = '0.0.0';
        $mockEdition = 'Test Edition';
        $mockUrl = 'http://test-url';

        $this->productMetadata->expects(static::once())->method('getName')->willReturn($mockName);
        $this->productMetadata->expects(static::once())->method('getVersion')->willReturn($mockVersion);
        $this->productMetadata->expects(static::once())->method('getEdition')->willReturn($mockEdition);
        $this->urlBuilder->expects(static::once())->method('getUrl')->with('*/*/*')->willReturn($mockUrl);

        $configValues = [
            'timeout'   => 2,
            'useragent' => $mockName . '/' . $mockVersion . ' (' . $mockEdition . ')',
            'referer'   => $mockUrl
        ];

        $lastUpdate = 0;
        $this->cacheManager->expects(static::once())->method('load')->will((static::returnValue($lastUpdate)));
        $this->curlFactory->expects(static::at(0))->method('create')->will(static::returnValue($this->curl));
        $this->curl->expects(static::once())->method('setConfig')->with($configValues)->willReturnSelf();
        $this->curl->expects(static::once())->method('read')->will(static::returnValue($curlRequest));
        $this->backendConfig->expects(static::at(0))->method('getValue')->will(static::returnValue('1'));
        $this->backendConfig->expects(static::once())->method('isSetFlag')->will(static::returnValue(false));
        $this->backendConfig->expects(static::at(1))->method('getValue')
            ->will(static::returnValue('http://feed.magento.com'));
        $this->deploymentConfig->expects(static::once())->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE)
            ->will(static::returnValue('Sat, 6 Sep 2014 16:46:11 UTC'));
        if ($callInbox) {
            $this->inboxFactory->expects(static::once())->method('create')
                ->will(static::returnValue($this->inboxModel));
            $this->inboxModel->expects(static::once())
                ->method('parse')
                ->with(
                    static::callback(
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
                ->will(static::returnSelf());
        } else {
            $this->inboxFactory->expects(static::never())->method('create');
            $this->inboxModel->expects(static::never())->method('parse');
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
                //phpcs:disable
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
                //phpcs:enable
            ],
        ];
    }
}
