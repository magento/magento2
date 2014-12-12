<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Tests Magento\Webapi\Model\Authorization\GuestUserContext
 */
class GuestUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Webapi\Model\Authorization\GuestUserContext
     */
    protected $guestUserContext;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->guestUserContext = $this->objectManager->getObject(
            'Magento\Webapi\Model\Authorization\GuestUserContext'
        );
    }

    public function testGetUserId()
    {
        $this->assertEquals(null, $this->guestUserContext->getUserId());
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_GUEST, $this->guestUserContext->getUserType());
    }
}
