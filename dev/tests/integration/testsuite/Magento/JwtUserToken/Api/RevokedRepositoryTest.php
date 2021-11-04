<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\JwtUserToken\Api\Data\Revoked;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RevokedRepositoryTest extends TestCase
{
    /**
     * @var RevokedRepositoryInterface
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(RevokedRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $id = 169691;
        $type = UserContextInterface::USER_TYPE_CUSTOMER;
        $ts = time();

        $this->model->saveRevoked(new Revoked($type, $id, $ts));

        $found = $this->model->findRevoked($type, $id);
        $this->assertNotNull($found);
        $this->assertEquals($ts, $found->getBeforeTimestamp());
    }
}
