<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\AdminNotification\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class FeedTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\AdminNotification\Model\Feed */
    protected $feed;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $inboxFactory;

    /** @var \Magento\AdminNotification\Model\Inbox|\PHPUnit_Framework_MockObject_MockObject */
    protected $inboxModel;

    /** @var \Magento\Framework\HTTP\Adapter\CurlFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $curlFactory;

    /** @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit_Framework_MockObject_MockObject */
    protected $curl;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendConfig;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    protected function setUp()
    {
        $this->inboxFactory = $this->getMock('Magento\AdminNotification\Model\InboxFactory', ['create']);
        $this->curlFactory = $this->getMock('\Magento\Framework\HTTP\Adapter\CurlFactory', ['create']);
        $this->curl = $this->getMock('\Magento\Framework\HTTP\Adapter\Curl', ['read']);
        $this->appState = $this->getMock('\Magento\Framework\App\State', ['getInstallDate'], [], '', false);
        $this->inboxModel = $this->getMock(
            '\Magento\AdminNotification\Model\Inbox',
            [
                '__wakeup',
                'parse'
            ],
            [],
            '',
            false
        );
        $this->backendConfig = $this->getMock(
            'Magento\Backend\App\ConfigInterface',
            [
                'getValue',
                'setValue',
                'isSetFlag'
            ]
        );
        $this->cacheManager = $this->getMock(
            '\Magento\Framework\App\CacheInterface',
            [
                'load',
                'getFrontend',
                'remove',
                'save',
                'clean'
            ]
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->feed = $this->objectManagerHelper->getObject(
            'Magento\AdminNotification\Model\Feed',
            [
                'backendConfig' => $this->backendConfig,
                'cacheManager' => $this->cacheManager,
                'inboxFactory' => $this->inboxFactory,
                'appState' => $this->appState,
                'curlFactory' => $this->curlFactory
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
        $lastUpdate = 1410121748;
        $this->curlFactory->expects($this->at(0))->method('create')->will($this->returnValue($this->curl));
        $this->curl->expects($this->any())->method('read')->will($this->returnValue($curlRequest));
        $this->backendConfig->expects($this->at(0))->method('getValue')->will($this->returnValue('1'));
        $this->backendConfig->expects($this->once())->method('isSetFlag')->will($this->returnValue(false));
        $this->backendConfig->expects($this->at(1))->method('getValue')
            ->will($this->returnValue('http://feed.magento.com'));
        $this->cacheManager->expects($this->once())->method('load')->will(($this->returnValue($lastUpdate)));
        $this->appState->expects($this->once())->method('getInstallDate')->will(($this->returnValue($lastUpdate)));
        if ($callInbox) {
            $this->inboxFactory->expects($this->once())->method('create')
                ->will(($this->returnValue($this->inboxModel)));
            $this->inboxModel->expects($this->once())->method('parse')->will($this->returnSelf());
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
                        </rss>'
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
            ]
        ];
    }

}
