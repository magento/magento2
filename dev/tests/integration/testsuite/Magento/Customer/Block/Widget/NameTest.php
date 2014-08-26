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
namespace Magento\Customer\Block\Widget;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test \Magento\Customer\Block\Widget\Name
 */
class NameTest extends \PHPUnit_Framework_TestCase
{
    /** @var Name */
    protected $_block;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->_block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Widget\Name'
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtmlSimpleName()
    {
        /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->get('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $customerBuilder->setFirstname('Jane');
        $customerBuilder->setLastname('Doe');
        $this->_block->setObject($customerBuilder->create());

        $html = $this->_block->toHtml();

        $this->assertContains('title="First Name"', $html);
        $this->assertContains('value="Jane"', $html);
        $this->assertContains('title="Last Name"', $html);
        $this->assertContains('value="Doe"', $html);
        $this->assertNotContains('title="Middle Name/Initial"', $html);
        $this->assertNotContains('title="Prefix"', $html);
        $this->assertNotContains('title="Suffix"', $html);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/attribute_user_fullname.php
     */
    public function testToHtmlFancyName()
    {
        /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->get('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $customerBuilder->setPrefix(
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
        $this->_block->setObject($customerBuilder->create());

        $html = $this->_block->toHtml();

        $this->assertContains('title="First Name"', $html);
        $this->assertContains('value="Jane"', $html);
        $this->assertContains('title="Last Name"', $html);
        $this->assertContains('value="Doe"', $html);
        $this->assertContains('title="Middle Name/Initial"', $html);
        $this->assertContains('value="Roe"', $html);
        $this->assertContains('title="Prefix"', $html);
        $this->assertContains('value="Dr."', $html);
        $this->assertContains('title="Suffix"', $html);
        $this->assertContains('value="Ph.D."', $html);
    }
}
