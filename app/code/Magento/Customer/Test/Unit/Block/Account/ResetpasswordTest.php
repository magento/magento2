<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Model\AccountManagement;

/**
 * Test class for \Magento\Customer\Block\Account\Resetpassword
 */
class ResetpasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Customer\Block\Account\Resetpassword
     */
    protected $block;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->scopeConfigMock =  $this->getMock(
            \Magento\Framework\App\Config::class,
            ['getValue'],
            [],
            '',
            false
        );

        /** @var \Magento\Framework\View\Element\Template\Context | \PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            [],
            [],
            '',
            false
        );
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManager->getObject(
            '\Magento\Customer\Block\Account\Resetpassword',
            ['context' => $context]
        );
    }

    /**
     * @return void
     */
    public function testGetMinimumPasswordLength()
    {
        $minimumPasswordLength = '8';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH)
            ->willReturn($minimumPasswordLength);

        $this->assertEquals($minimumPasswordLength, $this->block->getMinimumPasswordLength());
    }

    /**
     * @return void
     */
    public function testGetRequiredCharacterClassesNumber()
    {
        $requiredCharacterClassesNumber = '4';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER)
            ->willReturn($requiredCharacterClassesNumber);

        $this->assertEquals($requiredCharacterClassesNumber, $this->block->getRequiredCharacterClassesNumber());
    }
}
