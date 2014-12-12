<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\App\PageCache;

/**
 * Class FormKeyTest
 */
class FormKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Version instance
     *
     * @var FormKey
     */
    protected $formKey;

    /**
     * Cookie mock
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * Create cookie mock and FormKey instance
     */
    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $this->formKey = $objectManager->getObject(
            'Magento\Framework\App\PageCache\FormKey',
            ['cookieManager' => $this->cookieManagerMock]
        );
    }

    public function testGet()
    {
        //Data
        $formKey = 'test_from_key';

        //Verification
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(\Magento\Framework\App\PageCache\FormKey::COOKIE_NAME)
            ->will($this->returnValue($formKey));

        $this->assertEquals($formKey, $this->formKey->get());
    }
}
