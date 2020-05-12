<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Model\System\Config\Backend;

use Magento\Contact\Model\System\Config\Backend\Links;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Contact\Model\System\Config\Backend\Links
 */
class LinksTest extends TestCase
{
    /**
     * @var Links
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = (new ObjectManagerHelper($this))->getObject(
            Links::class
        );
    }

    /**
     * Test getIdentities
     */
    public function testGetIdentities(): void
    {
        $this->assertIsArray($this->model->getIdentities());
    }
}
