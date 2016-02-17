<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Form;

/**
 * Test class for \Magento\Customer\Block\Form\Edit
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\AccountManagement
     */
    protected $accountManagementHelperMock;

    /**
     * @var \Magento\Customer\Block\Form\Edit
     */
    protected $block;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->accountManagementHelperMock =  $this->getMock(
            '\Magento\Customer\Helper\AccountManagement',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManager->getObject(
            '\Magento\Customer\Block\Form\Edit',
            ['AccountManagementHelper' => $this->accountManagementHelperMock]
        );
    }

    /**
     * @return void
     */
    public function testGetMinimumPasswordLength()
    {
        $minimumPasswordLength = '8';

        $this->accountManagementHelperMock->expects($this->once())
            ->method('getMinimumPasswordLength')
            ->willReturn($minimumPasswordLength);

        $this->assertEquals($minimumPasswordLength, $this->block->getMinimumPasswordLength());
    }

    /**
     * @return void
     */
    public function testGetRequiredCharacterClassesNumber()
    {
        $requiredCharacterClassesNumber = '4';

        $this->accountManagementHelperMock->expects($this->once())
            ->method('getRequiredCharacterClassesNumber')
            ->willReturn($requiredCharacterClassesNumber);

        $this->assertEquals($requiredCharacterClassesNumber, $this->block->getRequiredCharacterClassesNumber());
    }
}
