<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $localeResolver = $this->getMock('\Magento\Framework\Locale\ResolverInterface', [], [], '', false);
        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $string = $this->getMock('\Magento\Framework\Stdlib\String', [], [], '', false);
        $config = $this->getMock('Magento\Ogone\Model\Config', [], [], '', false);
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $loggerFactory = $this->getMock('\Magento\Framework\Logger\AdapterFactory', [], [], '', false);
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
