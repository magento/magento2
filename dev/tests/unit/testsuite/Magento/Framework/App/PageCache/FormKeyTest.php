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
     * @var \Magento\Framework\Stdlib\CookieManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * Create cookie mock and FormKey instance
     */
    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->cookieManagerMock = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManager')
            ->disableOriginalConstructor()->getMock();
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
