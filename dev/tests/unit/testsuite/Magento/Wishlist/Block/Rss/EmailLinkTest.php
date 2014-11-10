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

namespace Magento\Wishlist\Block\Rss;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class EmailLinkTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Wishlist\Block\Rss\EmailLink */
    protected $link;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistHelper;

    /** @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    protected function setUp()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', ['getId', 'getSharingCode'], [], '', false);
        $wishlist->expects($this->any())->method('getId')->will($this->returnValue(5));
        $wishlist->expects($this->any())->method('getSharingCode')->will($this->returnValue('somesharingcode'));
        $customer = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $customer->expects($this->any())->method('getId')->will($this->returnValue(8));
        $customer->expects($this->any())->method('getEmail')->will($this->returnValue('test@example.com'));

        $this->wishlistHelper = $this->getMock(
            'Magento\Wishlist\Helper\Data',
            ['getWishlist', 'getCustomer'],
            [],
            '',
            false
        );
        $this->wishlistHelper->expects($this->any())->method('getWishlist')->will($this->returnValue($wishlist));
        $this->wishlistHelper->expects($this->any())->method('getCustomer')->will($this->returnValue($customer));

        $this->urlBuilder = $this->getMock('Magento\Framework\App\Rss\UrlBuilderInterface');
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->link = $this->objectManagerHelper->getObject(
            'Magento\Wishlist\Block\Rss\EmailLink',
            [
                'wishlistHelper' => $this->wishlistHelper,
                'rssUrlBuilder' => $this->urlBuilder
            ]
        );
    }

    public function testGetLink()
    {
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with($this->equalTo(array(
                'type' => 'wishlist',
                'data' => 'OCx0ZXN0QGV4YW1wbGUuY29t',
                '_secure' => false,
                'wishlist_id' => 5,
                'sharing_code' => 'somesharingcode'
            )))
            ->will($this->returnValue('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5'));
        $this->assertEquals('http://url.com/rss/feed/index/type/wishlist/wishlist_id/5', $this->link->getLink());
    }

}
