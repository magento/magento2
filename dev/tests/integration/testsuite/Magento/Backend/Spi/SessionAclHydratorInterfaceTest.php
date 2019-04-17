<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\Spi;

use Magento\Framework\AclFactory;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Acl\Builder as AclBuilder;

/**
 * Test for session hydrator.
 */
class SessionAclHydratorInterfaceTest extends TestCase
{
    /**
     * @var SessionAclHydratorInterface
     */
    private $hydrator;

    /**
     * @var AclBuilder
     */
    private $aclBuilder;

    /**
     * @var AclFactory
     */
    private $aclFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->hydrator = $objectManager->get(SessionAclHydratorInterface::class);
        $this->aclBuilder = $objectManager->get(AclBuilder::class);
        $this->aclFactory = $objectManager->get(AclFactory::class);
    }

    /**
     * Test that ACL data is preserved.
     */
    public function testHydrate()
    {
        $acl = $this->aclBuilder->getAcl();
        $data = $this->hydrator->extract($acl);
        $this->hydrator->hydrate($built = $this->aclFactory->create(), $data);
        $this->assertEquals($acl->getRoles(), $built->getRoles());
        $this->assertEquals($acl->getResources(), $built->getResources());
    }
}
