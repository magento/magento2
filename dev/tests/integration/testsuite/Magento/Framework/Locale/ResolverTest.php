<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Locale;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLocale()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        \Zend_Locale_Data::removeCache();
        $this->assertNull(\Zend_Locale_Data::getCache());
        $model = $objectManager->create('Magento\Framework\Locale\ResolverInterface', ['locale' => 'some_locale']);
        $this->assertInstanceOf('Zend_Locale', $model->getLocale());
        $this->assertInstanceOf('Zend_Cache_Core', \Zend_Locale_Data::getCache());
    }
}
