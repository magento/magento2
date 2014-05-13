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

namespace Magento\Catalog\Helper\Product;

/**
 * Class CompareTest
 */
class CompareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $compareHelper;

    /**
     * @var \Magento\Framework\App\Helper\Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Url | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Core\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->urlBuilder = $this->getMock('Magento\Framework\Url', array('getUrl'), array(), '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', array('getServer'), array(), '', false);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $this->context = $this->getMock(
            'Magento\Framework\App\Helper\Context',
            array('getUrlBuilder', 'getRequest'),
            array(),
            '',
            false
        );
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilder));
        $this->context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->postDataHelper = $this->getMock(
            'Magento\Core\Helper\PostData',
            array('getPostData'),
            array(),
            '',
            false
        );

        $this->compareHelper = $objectManager->getObject(
            'Magento\Catalog\Helper\Product\Compare',
            array('context' => $this->context, 'coreHelper' => $this->postDataHelper)
        );
    }

    public function testGetPostDataRemove()
    {
        //Data
        $productId = 1;
        $removeUrl = 'catalog/product_compare/remove';
        $compareListUrl = 'catalog/product_compare';
        $postParams = array(
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->compareHelper
                ->urlEncode($compareListUrl),
            'product' => $productId
        );

        //Verification
        $this->urlBuilder->expects($this->at(0))
            ->method('getUrl')
            ->with($compareListUrl)
            ->will($this->returnValue($compareListUrl));
        $this->urlBuilder->expects($this->at(1))
            ->method('getUrl')
            ->with($removeUrl)
            ->will($this->returnValue($removeUrl));
        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($removeUrl, $postParams)
            ->will($this->returnValue(true));

        /** @var \Magento\Catalog\Model\Product | \PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock('Magento\Catalog\Model\Product', array('getId', '__wakeup'), array(), '', false);
        $product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));

        $this->assertTrue($this->compareHelper->getPostDataRemove($product));
    }

    public function testGetClearListUrl()
    {
        //Data
        $url = 'catalog/product_compare/clear';

        //Verification
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with($url)
            ->will($this->returnValue($url));

        $this->assertEquals($url, $this->compareHelper->getClearListUrl());
    }

    public function testGetPostDataClearList()
    {
        //Data
        $refererUrl = 'home/';
        $clearUrl = 'catalog/product_compare/clear';
        $postParams = array(
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->compareHelper->urlEncode($refererUrl)
        );

        //Verification
        $this->request->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->will($this->returnValue($refererUrl));

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with($clearUrl)
            ->will($this->returnValue($clearUrl));

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($clearUrl, $postParams)
            ->will($this->returnValue(true));

        $this->assertTrue($this->compareHelper->getPostDataClearList());
    }
}
