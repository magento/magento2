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
namespace Magento\Ogone\Model;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test protected method, which converts Magento internal charset (UTF-8) to the one, understandable
     * by Ogone (ISO-8859-1), and then encodes html entities
     */
    public function testTranslate()
    {
        /* Compose the string, which, when converted to ISO-8859-1, still looks like a valid UTF-8 string.
           So that the latter result of htmlentities() is different, depending on the encoding used for it. */
        $sourceString = 'Ë£';

        // Test protected method via reflection
        $storeManager = $this->getMock('Magento\Framework\StoreManagerInterface', array(), array(), '', false);
        $localeResolver = $this->getMock('\Magento\Framework\Locale\ResolverInterface', array(), array(), '', false);
        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface', array(), array(), '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $string = $this->getMock('\Magento\Framework\Stdlib\String', array(), array(), '', false);
        $config = $this->getMock('Magento\Ogone\Model\Config', array(), array(), '', false);
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', array(), array(), '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $loggerFactory = $this->getMock('\Magento\Framework\Logger\AdapterFactory', array(), array(), '', false);
        $object = new \Magento\Ogone\Model\Api(
            $eventManager,
            $paymentDataMock,
            $scopeConfig,
            $loggerFactory,
            $storeManager,
            $localeResolver,
            $urlBuilder,
            $string,
            $config
        );

        $method = new \ReflectionMethod('Magento\Ogone\Model\Api', '_translate');
        $method->setAccessible(true);

        $result = $method->invoke($object, $sourceString);
        $this->assertEquals('&Euml;&pound;', $result);
    }
}
