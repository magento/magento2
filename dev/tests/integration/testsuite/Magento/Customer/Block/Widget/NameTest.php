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
class NameTest extends \PHPUnit\Framework\TestCase
{
    /** @var Name */
    protected $_block;

    protected function setUp(): void
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

        $this->assertStringContainsString('title="First&#x20;Name"', $html);
        $this->assertStringContainsString('value="Jane"', $html);
        $this->assertStringContainsString('title="Last&#x20;Name"', $html);
        $this->assertStringContainsString('value="Doe"', $html);
        $this->assertStringNotContainsString('title="Middle&#x20;Name&#x2F;Initial"', $html);
        $this->assertStringNotContainsString('title="Name&#x20;Prefix"', $html);
        $this->assertStringNotContainsString('title="Name&#x20;Suffix"', $html);
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

        $this->assertStringContainsString('title="First&#x20;Name"', $html);
        $this->assertStringContainsString('value="Jane"', $html);
        $this->assertStringContainsString('title="Last&#x20;Name"', $html);
        $this->assertStringContainsString('value="Doe"', $html);
        $this->assertStringContainsString('title="Middle&#x20;Name&#x2F;Initial"', $html);
        $this->assertStringContainsString('value="Roe"', $html);
        $this->assertStringContainsString('title="Name&#x20;Prefix"', $html);
        $this->assertStringContainsString('value="Dr."', $html);
        $this->assertStringContainsString('title="Name&#x20;Suffix"', $html);
        $this->assertStringContainsString('value="Ph.D."', $html);
    }
}
