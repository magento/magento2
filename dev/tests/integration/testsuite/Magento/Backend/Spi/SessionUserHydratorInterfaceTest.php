<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\Spi;

use PHPUnit\Framework\TestCase;
use Magento\User\Model\UserFactory;
use Magento\User\Model\User;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestHelper;

/**
 * Test hydrator for user data in session.
 */
class SessionUserHydratorInterfaceTest extends TestCase
{
    /**
     * @var SessionUserHydratorInterface
     */
    private $hydrator;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->hydrator = $objectManager->get(SessionUserHydratorInterface::class);
        $this->userFactory = $objectManager->get(UserFactory::class);
    }

    /**
     * Make sure users' data is preserved during extract/hydrate.
     */
    public function testHydrate()
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $user->loadByUsername(TestHelper::ADMIN_NAME);

        $userData = $this->hydrator->extract($user);
        /** @var User $newUser */
        $newUser = $this->userFactory->create();
        $this->hydrator->hydrate($newUser, $userData);
        $this->assertEquals($user->getData(), $newUser->getData());
        $this->assertEquals($user->getRole()->getId(), $newUser->getRole()->getId());
    }
}
