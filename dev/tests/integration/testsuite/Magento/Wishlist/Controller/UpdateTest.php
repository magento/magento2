<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller;

use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Model\Item;
use Psr\Log\LoggerInterface;
use Zend\Http\Request;

/**
 * Tests updating wishlist item comment.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
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
     */
    public function testUpdateComment($postDescription, $postQty, $expectedResult, $presetComment)
    {
        $itemId = 1;
        $wishlistId = 1;

        if ($presetComment) {
            $item = $this->_objectManager->create(Item::class)->load($itemId);
            $item->setDescription($this->description);
            $item->save();
        }

        $formKey = $this->_objectManager->get(FormKey::class);
        $this->getRequest()->setPostValue(
            [
                'description' => $postDescription,
                'qty' => $postQty,
                'do' => '',
                'form_key' => $formKey->getFormKey()
            ]
        )->setMethod(Request::METHOD_POST);
        $this->dispatch('wishlist/index/update/wishlist_id/' . $wishlistId);

        $item = $this->_objectManager->create(Item::class)->load($itemId);

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
                'postDescription' => [1 => $this->description],
                'postQty' => [1 => '1'],
                'expectedResult' => $this->description,
                'presetComment' => false
            ],
            'test removing comment' => [
                'postDescription' => [1 => ''],
                'postQty' => [1 => '1'],
                'expectedResult' => '',
                'presetComment' => true
            ],
            'test not changing comment' => [
                'postDescription' => [],
                'postQty' => [1 => '1'],
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
        /** @var \Magento\Customer\Api\AccountManagementInterface $service */
        $service = $this->_objectManager->create(
            \Magento\Customer\Api\AccountManagementInterface::class
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
