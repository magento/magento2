<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Widget;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test \Magento\Customer\Block\Widget\Name
 * @magentoAppArea frontend
 */
class NameTest extends \PHPUnit_Framework_TestCase
{
    /** @var Name */
    protected $_block;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $this->_block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Widget\Name::class
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtmlSimpleName()
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        );
        $customerDataObject = $customerFactory->create();
        $customerDataObject->setFirstname('Jane');
        $customerDataObject->setLastname('Doe');
        $this->_block->setObject($customerDataObject);

        $html = $this->_block->toHtml();

        $this->assertContains('title="First&#x20;Name"', $html);
        $this->assertContains('value="Jane"', $html);
        $this->assertContains('title="Last&#x20;Name"', $html);
        $this->assertContains('value="Doe"', $html);
        $this->assertNotContains('title="Middle&#x20;Name&#x2F;Initial"', $html);
        $this->assertNotContains('title="Prefix"', $html);
        $this->assertNotContains('title="Suffix"', $html);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/attribute_user_fullname.php
     */
    public function testToHtmlFancyName()
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory */
        $customerFactory = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        );
        $customerDataObject = $customerFactory->create();
        $customerDataObject->setPrefix(
            'Dr.'
        )->setFirstname(
            'Jane'
        )->setMiddlename(
            'Roe'
        )->setLastname(
            'Doe'
        )->setSuffix(
            'Ph.D.'
        );
        $this->_block->setObject($customerDataObject);

        $html = $this->_block->toHtml();

        $this->assertContains('title="First&#x20;Name"', $html);
        $this->assertContains('value="Jane"', $html);
        $this->assertContains('title="Last&#x20;Name"', $html);
        $this->assertContains('value="Doe"', $html);
        $this->assertContains('title="Middle&#x20;Name&#x2F;Initial"', $html);
        $this->assertContains('value="Roe"', $html);
        $this->assertContains('title="Prefix"', $html);
        $this->assertContains('value="Dr."', $html);
        $this->assertContains('title="Suffix"', $html);
        $this->assertContains('value="Ph.D."', $html);
    }
}
