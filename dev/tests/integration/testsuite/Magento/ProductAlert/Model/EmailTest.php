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

namespace Magento\ProductAlert\Model;

class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ProductAlert\Model\Email
     */
    protected $_emailModel;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerAccountManagement = $this->_objectManager->create(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $this->_customerViewHelper = $this->_objectManager->create('Magento\Customer\Helper\View');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider customerFunctionDataProvider
     *
     * @param bool isCustomerIdUsed
     */
    public function testSend($isCustomerIdUsed)
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $this->_emailModel = $this->_objectManager->create('Magento\ProductAlert\Model\Email');

        /** @var \Magento\Store\Model\Website $website */
        $website = $this->_objectManager->create('Magento\Store\Model\Website');
        $website->load(1);
        $this->_emailModel->setWebsite($website);

        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customerRepository = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);

        if ($isCustomerIdUsed) {
            $this->_emailModel->setCustomerId(1);
        } else {
            $this->_emailModel->setCustomerData($customer);
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $product->load(1);

        $this->_emailModel->addPriceProduct($product);

        $this->_emailModel->send();

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = $this->_objectManager->get('Magento\TestFramework\Mail\Template\TransportBuilderMock');
        $this->assertStringMatchesFormat(
            '%AHello ' . $this->_customerViewHelper->getCustomerName($customer) . '%A',
            $transportBuilder->getSentMessage()->getBodyHtml()->getContent()
        );
    }

    public function customerFunctionDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
