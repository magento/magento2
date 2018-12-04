<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use Psr\Log\LoggerInterface;
use Zend\Http\Request;

/**
 * Tests updating wishlist item comment.
 *
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class UpdateTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ManagerInterface
     */
    private $messages;

    /**
     * @var View
     */
    private $customerViewHelper;

    /**
     * Description field value for wishlist item.
     *
     * @var string
     */
    private $description = 'some description';

    /**
     * Tests updating wishlist item comment.
     *
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     * @dataProvider commentDataProvider
     * @param string|null $postDescription
     * @param string $expectedResult
     * @param boolean $presetComment
     * @magentoDbIsolation disabled
     */
    public function testUpdateComment($postDescription, $expectedResult, $presetComment)
    {
        /** @var Customer $customer */
        $customer = $this->customerSession->getCustomer();
        /** @var Wishlist $wishlist */
        $wishlist = $this->_objectManager
            ->get(Wishlist::class)
            ->loadByCustomerId($customer->getId(), true);
        /** @var Item $item */
        $item = $wishlist->getItemCollection()->getFirstItem();

        if ($presetComment) {
            $item->setDescription($this->description);
            $item->save();
        }

        $formKey = $this->_objectManager->get(FormKey::class);
        $this->getRequest()->setPostValue(
            [
                'description' => isset($postDescription) ? [$item->getId() => $postDescription] : [],
                'qty' => isset($postDescription) ? [$item->getId() => 1] : [],
                'do' => '',
                'form_key' => $formKey->getFormKey()
            ]
        )->setMethod(Request::METHOD_POST);
        $this->dispatch('wishlist/index/update/wishlist_id/' . $wishlist->getId());

        // Reload item
        $item = $this->_objectManager->get(Item::class)->load($item->getId());
        self::assertEquals(
            $expectedResult,
            $item->getDescription()
        );
    }

    /**
     * Data provider for testUpdateComment.
     *
     * @return array
     */
    public function commentDataProvider()
    {

        return [
            'test adding comment' => [
                'postDescription' => $this->description,
                'expectedResult' => $this->description,
                'presetComment' => false
            ],
            'test removing comment' => [
                'postDescription' => '',
                'expectedResult' => '',
                'presetComment' => true
            ],
            'test not changing comment' => [
                'postDescription' => null,
                'expectedResult' => $this->description,
                'presetComment' => true
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();
        $logger = $this->createMock(LoggerInterface::class);
        $this->customerSession = $this->_objectManager->get(
            Session::class,
            [$logger]
        );
        /** @var AccountManagementInterface $service */
        $service = $this->_objectManager->create(
            AccountManagementInterface::class
        );
        $customer = $service->authenticate('customer@example.com', 'password');
        $this->customerSession->setCustomerDataAsLoggedIn($customer);

        $this->customerViewHelper = $this->_objectManager->create(View::class);

        $this->messages = $this->_objectManager->get(
            ManagerInterface::class
        );
    }

    protected function tearDown()
    {
        $this->customerSession->logout();
        $this->customerSession = null;
        parent::tearDown();
    }
}
