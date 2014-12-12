<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Block\Authorizenet\Form;

class CcTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Authorizenet\Block\Authorizenet\Form\Cc */
    protected $_block;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $objectManager->getObject('Magento\Authorizenet\Block\Authorizenet\Form\Cc');
        $methodMock = $this->getMock('Magento\Authorizenet\Model\Authorizenet', [], [], '', false);
        $this->_block->addData(['method' => $methodMock]);
    }

    public function testEscapeMessage()
    {
        $testString = 'Why don\'t you want to press "OK"?';
        $expected = htmlspecialchars($testString, ENT_QUOTES, 'UTF-8');
        $this->assertEquals($expected, $this->_block->escapeMessage($testString));
    }

    public function testGetWidgetInitData()
    {
        $widgetInitData = \Zend_Json::decode($this->_block->getWidgetInitData());
        $this->assertArrayHasKey('authorizenetAuthenticate', $widgetInitData);
        $this->assertArrayHasKey(
            'partialAuthorizationConfirmationMessage',
            $widgetInitData['authorizenetAuthenticate']
        );
        $this->assertArrayHasKey('cancelConfirmationMessage', $widgetInitData['authorizenetAuthenticate']);
        $this->assertArrayHasKey('cancelUrl', $widgetInitData['authorizenetAuthenticate']);
    }
}
