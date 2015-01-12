<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Block\Header;

/**
 * @magentoDataFixture Magento/Persistent/_files/persistent.php
 */
class AdditionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Block\Header\Additional
     */
    protected $_block;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Persistent\Helper\Session $persistentSessionHelper */
        $this->_persistentSessionHelper = $this->_objectManager->create('Magento\Persistent\Helper\Session');

        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');

        $this->_block = $this->_objectManager->create('Magento\Persistent\Block\Header\Additional');
    }

    /**
     * @magentoConfigFixture current_store persistent/options/customer 1
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        $this->_customerSession->loginById(1);
        /** @var \Magento\Customer\Helper\View $customerViewHelper */
        $customerViewHelper = $this->_objectManager->create(
            'Magento\Customer\Helper\View'
        );

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        /** @var \Magento\Framework\Escaper $escaper */
        $escaper = $this->_objectManager->create(
            'Magento\Framework\Escaper'
        );
        $persistentName = $escaper->escapeHtml(
            $customerViewHelper->getCustomerName(
                $customerRepository->getById(
                    $this->_persistentSessionHelper->getSession()->getCustomerId()
                )
            )
        );

        $translation = __('(Not %1?)', $persistentName);

        $this->assertStringMatchesFormat(
            '%A<span>%A<a%Ahref="' . $this->_block->getHref() . '"%A>' . $translation . '</a>%A</span>%A',
            $this->_block->toHtml()
        );
        $this->_customerSession->logout();
    }
}
