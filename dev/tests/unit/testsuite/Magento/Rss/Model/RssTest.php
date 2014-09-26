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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rss\Model;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rss\Model\Rss
     */
    protected $rss;

    /**
     * @var array
     */
    protected $feedData = array(
        'title' => 'Feed Title',
        'link' => 'http://magento.com/rss/link',
        'description' => 'Feed Description',
        'charset' => 'UTF-8',
        'entries' => array(
            array(
                'title' => 'Feed 1 Title',
                'link' => 'http://magento.com/rss/link/id/1',
                'description' => 'Feed 1 Description'
            )
        )
    );

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheInterface;

    protected function setUp()
    {
        $this->cacheInterface = $this->getMock('Magento\Framework\App\CacheInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            'Magento\Rss\Model\Rss',
            [
                'cache' => $this->cacheInterface
            ]
        );
    }

    public function testGetFeeds()
    {
        $dataProvider = $this->getMock('Magento\Framework\App\Rss\DataProviderInterface');
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->any())->method('getRssData')->will($this->returnValue($this->feedData));

        $this->rss->setDataProvider($dataProvider);

        $this->cacheInterface->expects($this->once())->method('load')->will($this->returnValue(false));
        $this->cacheInterface->expects($this->once())->method('save')->will($this->returnValue(true));

        $this->assertEquals($this->feedData, $this->rss->getFeeds());
    }

    public function testGetFeedsWithCache()
    {
        $dataProvider = $this->getMock('Magento\Framework\App\Rss\DataProviderInterface');
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->never())->method('getRssData');

        $this->rss->setDataProvider($dataProvider);

        $this->cacheInterface->expects($this->once())->method('load')
            ->will($this->returnValue(serialize($this->feedData)));
        $this->cacheInterface->expects($this->never())->method('save');

        $this->assertEquals($this->feedData, $this->rss->getFeeds());
    }

    public function testCreateRssXml()
    {
        $dataProvider = $this->getMock('Magento\Framework\App\Rss\DataProviderInterface');
        $dataProvider->expects($this->any())->method('getCacheKey')->will($this->returnValue('cache_key'));
        $dataProvider->expects($this->any())->method('getCacheLifetime')->will($this->returnValue(100));
        $dataProvider->expects($this->any())->method('getRssData')->will($this->returnValue($this->feedData));

        $this->rss->setDataProvider($dataProvider);
        $result = $this->rss->createRssXml();
        $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $result);
        $this->assertContains('<title><![CDATA[Feed Title]]></title>', $result);
        $this->assertContains('<title><![CDATA[Feed 1 Title]]></title>', $result);
        $this->assertContains('<link>http://magento.com/rss/link</link>', $result);
        $this->assertContains('<link>http://magento.com/rss/link/id/1</link>', $result);
        $this->assertContains('<description><![CDATA[Feed Description]]></description>', $result);
        $this->assertContains('<description><![CDATA[Feed 1 Description]]></description>', $result);
    }
}
