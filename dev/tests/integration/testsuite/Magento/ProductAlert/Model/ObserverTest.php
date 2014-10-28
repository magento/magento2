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

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerSession = $this->_objectManager->get(
            'Magento\Customer\Model\Session'
        );
        $service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerAccountService'
        );
        $customer = $service->authenticate('customer@example.com', 'password');
        $this->_customerSession->setCustomerDataAsLoggedIn($customer);
        $this->_customerViewHelper = $this->_objectManager->create('Magento\Customer\Helper\View');
    }

    /**
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     *
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testProcess()
    {
        $this->_objectManager->configure(
            [
                'Magento\ProductAlert\Model\Observer' => [
                    'arguments' => [
                        'transportBuilder' => [
                            'instance' => 'Magento\TestFramework\Mail\Template\TransportBuilderMock'
                        ]
                    ]
                ],
                'Magento\ProductAlert\Model\Email' => [
                    'arguments' => [
                        'transportBuilder' => [
                            'instance' => 'Magento\TestFramework\Mail\Template\TransportBuilderMock'
                        ]
                    ]
                ],
                'preferences' => [
                    'Magento\Framework\Mail\TransportInterface' => 'Magento\TestFramework\Mail\TransportInterfaceMock',
                    'Magento\TestFramework\Mail\Template\TransportBuilder' =>
                        'Magento\TestFramework\Mail\Template\TransportBuilderMock'
                ]
            ]
        );
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $observer = $this->_objectManager->get('Magento\ProductAlert\Model\Observer');
        $observer->process();

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = $this->_objectManager->get('Magento\TestFramework\Mail\Template\TransportBuilderMock');

        $this->assertStringMatchesFormat(
            '%AHello %A'
            . $this->_customerViewHelper->getCustomerName($this->_customerSession->getCustomerDataObject()) . ',%A',
            $transportBuilder->getSentMessage()->getBodyHtml()->getContent()
        );
    }
}
