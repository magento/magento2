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

namespace Magento\Directory\Block;

class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Block\Currency
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    public function setUp()
    {
        $this->urlBuilder = $this->getMock(
            '\Magento\Framework\UrlInterface\Proxy',
            array('getUrl'),
            array(),
            '',
            false
        );
        $this->urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));

        /** @var \Magento\Framework\View\Element\Template\Context $context */
        $context = $this->getMock(
            '\Magento\Framework\View\Element\Template\Context',
            array('getUrlBuilder'),
            array(),
            '',
            false
        );
        $context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($this->urlBuilder));

        /** @var \Magento\Directory\Model\CurrencyFactory $currencyFactory */
        $currencyFactory = $this->getMock('\Magento\Directory\Model\CurrencyFactory', array(), array(), '', false);
        $this->postDataHelper = $this->getMock('\Magento\Core\Helper\PostData', array(), array(), '', false);

        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolver */
        $localeResolver = $this->getMock('\Magento\Framework\Locale\ResolverInterface', array(), array(), '', false);

        $this->object = new Currency(
            $context,
            $currencyFactory,
            $this->postDataHelper,
            $localeResolver
        );
    }

    public function testGetSwitchCurrencyPostData()
    {
        $expectedResult = 'post_data';
        $expectedCurrencyCode = 'test';
        $switchUrl = 'directory/currency/switch';

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($this->equalTo($switchUrl), $this->equalTo(['currency' => $expectedCurrencyCode]))
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->object->getSwitchCurrencyPostData($expectedCurrencyCode));
    }
}
