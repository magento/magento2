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

namespace Magento\Rss\Block\Catalog;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Rss\Block\Catalog\Review */
    protected $review;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $rssFactoryMock;

    /** @var \Magento\Framework\Model\Resource\Iterator|\PHPUnit_Framework_MockObject_MockObject */
    protected $iteratorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $reviewFactoryMock;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface */
    protected $urlInterfaceMock;

    protected function setUp()
    {
        $this->urlInterfaceMock = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $this->contextMock = $this->getMock('Magento\Backend\Block\Context', [], [], '', false);
        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlInterfaceMock));

        $this->rssFactoryMock = $this->getMock('Magento\Rss\Model\RssFactory');
        $this->iteratorMock = $this->getMock('Magento\Framework\Model\Resource\Iterator', [], [], '', false);
        $this->reviewFactoryMock = $this->getMock('Magento\Review\Model\ReviewFactory');
        $this->storeManagerMock = $this->getMock(
            'Magento\Store\Model\StoreManager',
            ['getStore', 'getName'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->review = $this->objectManagerHelper->getObject(
            'Magento\Rss\Block\Catalog\Review',
            [
                'context' => $this->contextMock,
                'rssFactory' => $this->rssFactoryMock,
                'resourceIterator' => $this->iteratorMock,
                'reviewFactory' => $this->reviewFactoryMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testAddReviewItemXmlCallback()
    {
        $row = [
            'entity_id' => 1,
            'store_id' => 2,
            'review_id' => 3,
            'name' => 'Product Name',
            'title' => 'Review Summary',
            'detail' => 'Test of a review',
            'nickname' => 'User Name',
        ];
        $productUrl = 'http://product.url';
        $reviewUrl = 'http://review.url';


        $reviewModel = $this->getMock('Magento\Review\Model\Review', [], [], '', false);
        $reviewModel->expects($this->once())
            ->method('getProductUrl')
            ->with($this->equalTo($row['entity_id']), $this->equalTo($row['store_id']))
            ->will($this->returnValue($productUrl));

        $this->urlInterfaceMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo('review/product/edit/'),
                $this->equalTo(array('id' => $row['review_id'], '_secure' => true, '_nosecret' => true))
            )
            ->will($this->returnValue($reviewUrl));

        $storeName = 'Store Name';
        $this->storeManagerMock->expects($this->once())->method('getStore')
            ->with($this->equalTo($row['store_id']))->will($this->returnSelf());
        $this->storeManagerMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($storeName));
        $rssObj = $this->getMock('Magento\Rss\Model\Rss', [], [], '', false);
        $description = '<p>' . __('Product: <a href="%1" target="_blank">%2</a> <br/>', $productUrl, $row['name'])
            . __('Summary of review: %1 <br/>', $row['title']) . __('Review: %1 <br/>', $row['detail'])
            . __('Store: %1 <br/>', $storeName)
            . __('Click <a href="%1">here</a> to view the review.', $reviewUrl)
            . '</p>';
        $rssObj->expects($this->once())
            ->method('_addEntry')
            ->with(
                $this->equalTo(
                    [
                        'title' => __('Product: "%1" reviewed by: %2', $row['name'], $row['nickname']),
                        'link' => 'test',
                        'description' => $description
                    ]
                )
            )
            ->will($this->returnSelf());

        $args = [
            'row' => $row,
            'reviewModel' => $reviewModel,
            'rssObj' => $rssObj
        ];

        $this->assertNull($this->review->addReviewItemXmlCallback($args));
    }
}
