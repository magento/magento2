<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ogone\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_VALUE = 'abcdef1234567890';

    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('Magento\Ogone\Model\Config', [
                'scopeConfig' => $this->_scopeConfig
            ]);
    }

    public function testGetShaInCode()
    {
        $this->_scopeConfig->expects($this->any())->method('getValue')->with('payment/ogone/secret_key_in')->will(
            $this->returnValue(self::EXPECTED_VALUE)
        );
        $this->assertEquals(self::EXPECTED_VALUE, $this->_model->getShaInCode());
    }

    public function testGetShaOutCode()
    {
        $this->_scopeConfig->expects($this->any())->method('getValue')->with('payment/ogone/secret_key_out')->will(
            $this->returnValue(self::EXPECTED_VALUE)
        );
        $this->assertEquals(self::EXPECTED_VALUE, $this->_model->getShaOutCode());
    }
}
