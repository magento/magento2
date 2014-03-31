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
 * @category    Magento
 * @package     Magento_Wishlist
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Wishlist\Controller;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testControllerImplementsProductViewInterface()
    {
        $this->assertInstanceOf(
            'Magento\Catalog\Controller\Product\View\ViewInterface',
            $this->getMock('Magento\Wishlist\Controller\Index', array(), array(), '', false)
        );
    }

    public function testCartAction()
    {
        $request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);

        $wishlistItem = $this->getMock(
            'Magento\Object',
            array('load', 'getId', 'mergeBuyRequest', 'addToCart', 'getProduct'),
            array(),
            '',
            false
        );
        $wishlistItem->expects($this->once())->method('load')->will($this->returnValue($wishlistItem));
        $wishlistItem->expects($this->once())->method('getId')->will($this->returnValue(1));
        $wishlistItem->expects($this->once())->method('getProduct')->will($this->returnValue($wishlistItem));

        $objectManager = $this->getMock('Magento\ObjectManager');

        $locale = $this->getMock('Magento\Locale\Resolver', array(), array(), '', false);

        $optionCollection = $this->getMock(
            'Magento\Wishlist\Model\Resource\Item\Option\Collection',
            array('addItemFilter', 'getOptionsByItem'),
            array(),
            '',
            false
        );
        $optionCollection->expects(
            $this->once()
        )->method(
            'addItemFilter'
        )->will(
            $this->returnValue($optionCollection)
        );

        $cart = $this->getMock(
            'Magento\Checkout\Model\Cart',
            array('save', 'getQuote', 'collectTotals'),
            array(),
            '',
            false
        );
        $cart->expects($this->once())->method('save')->will($this->returnValue($cart));
        $cart->expects($this->any())->method('getQuote')->will($this->returnValue($cart));

        $option = $this->getMock('Magento\Object', array('getCollection'), array(), '', false);
        $option->expects($this->once())->method('getCollection')->will($this->returnValue($optionCollection));

        $product = $this->getMock('Magento\Catalog\Helper\Product', array(), array(), '', false);

        $escaper = $this->getMock('Magento\Excaper', array('escapeHtml'), array(), '', false);

        $wishlistHelper = $this->getMock(
            'Magento\Wishlist\Helper\Data',
            array('getShouldRedirectToCart', 'calculate', 'getCustomer'),
            array(),
            '',
            false
        );

        $mapGet = array(
            array('Magento\Locale\ResolverInterface', $locale),
            array('Magento\Checkout\Model\Cart', $cart),
            array('Magento\Catalog\Helper\Product', $product),
            array('Magento\Escaper', $escaper),
            array('Magento\Wishlist\Helper\Data', $wishlistHelper),
            array('Magento\Checkout\Helper\Cart', $wishlistHelper)
        );

        $mapCreate = array(
            array('Magento\Wishlist\Model\Item', array(), $wishlistItem),
            array('Magento\Wishlist\Model\Item\Option', array(), $option)
        );

        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap($mapGet));
        $objectManager->expects($this->any())->method('create')->will($this->returnValueMap($mapCreate));

        $controller = $this->_factory($request, $response, $objectManager);

        $controller->cartAction();
    }

    /**
     * Create the tested object
     *
     * @param \Magento\App\Request\Http $request
     * @param \Magento\App\Response\Http|null $response
     * @param \Magento\ObjectManager|null $objectManager
     * @return \Magento\Wishlist\Controller\Index
     */
    protected function _factory($request, $response = null, $objectManager = null)
    {
        if (!$response) {
            /** @var $response \Magento\App\ResponseInterface */
            $response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
            $response->headersSentThrowsException = false;
        }
        if (!$objectManager) {
            $objectManager = new \Magento\ObjectManager\ObjectManager();
        }
        $rewriteFactory = $this->getMock('Magento\Core\Model\Url\RewriteFactory', array('create'), array(), '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $varienFront = $helper->getObject('Magento\App\FrontController', array('rewriteFactory' => $rewriteFactory));

        $arguments = array(
            'request' => $request,
            'response' => $response,
            'objectManager' => $objectManager,
            'frontController' => $varienFront
        );
        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);

        $wishlistModel = $this->getMock('\Magento\Wishlist\Model\Wishlist', array(), array(), '', false);

        $coreRegistry = $this->getMock('\Magento\Registry', array('registry'), array(), '', false);
        $coreRegistry->expects($this->once())->method('registry')->will($this->returnValue($wishlistModel));

        $messageManager = $this->getMock('\Magento\Message\Manager', array(), array(), '', false);

        return $helper->getObject(
            'Magento\Wishlist\Controller\Index',
            array('context' => $context, 'coreRegistry' => $coreRegistry, 'messageManager' => $messageManager)
        );
    }
}
