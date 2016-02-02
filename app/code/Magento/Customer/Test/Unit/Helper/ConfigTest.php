<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Helper;

/**
 * Class ConfigTest
 * @package Magento\Customer\Test\Unit\Helper
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Customer\Helper\Config
     */
    protected $helper;

    protected function setUp()
    {
        $this->scopeConfigMock =  $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            'Magento\Customer\Helper\Config',
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * Test get minimum password length
     * @return void
     */
    public function testGetMinimumPasswordLength()
    {
        $minimumPasswordLength = '8';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Customer\Helper\Config::XML_PATH_MINIMUM_PASSWORD_LENGTH)
            ->willReturn($minimumPasswordLength);
        $this->assertEquals($minimumPasswordLength, $this->helper->getMinimumPasswordLength());
    }

    /**
     * Test get required character classes number
     * @return void
     */
    public function testGetRequiredCharacterClassesNumber()
    {
        $requiredCharacterClassesNumber = '4';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Customer\Helper\Config::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER)
            ->willReturn($requiredCharacterClassesNumber);
        $this->assertEquals($requiredCharacterClassesNumber, $this->helper->getRequiredCharacterClassesNumber());
    }
}
