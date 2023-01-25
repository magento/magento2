<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Authorization\Test\Unit\Policy;

use Magento\Framework\Authorization\Policy\DefaultPolicy;
use PHPUnit\Framework\TestCase;

class DefaultTest extends TestCase
{
    /**
     * @var DefaultPolicy
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new DefaultPolicy();
    }

    public function testIsAllowedReturnsTrueForAnyResource()
    {
        $this->assertTrue($this->_model->isAllowed('any_role', 'any_resource'));
    }
}
