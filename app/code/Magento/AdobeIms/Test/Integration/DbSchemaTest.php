<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Setup\Declaration\Schema\UpToDateDeclarativeSchema;

/**
 * Test for declarative schema setup
 */
class DbSchemaTest extends TestCase
{
    /**
     * @var UpToDateDeclarativeSchema
     */
    private $validator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->validator = Bootstrap::getObjectManager()->get(UpToDateDeclarativeSchema::class);
    }

    /**
     * Test for db schema
     */
    public function testDbSchemaUpToDate(): void
    {
        $this->assertTrue($this->validator->isUpToDate());
    }
}
