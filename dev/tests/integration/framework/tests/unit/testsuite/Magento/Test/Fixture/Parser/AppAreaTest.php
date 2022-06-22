<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    AppArea('adminhtml')
]
class AppAreaTest extends TestCase
{
    #[
        AppArea('frontend')
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\AppArea();
        $this->assertEquals(
            [['area' => 'frontend']],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        AppArea('webapi_rest')
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\AppArea();
        $this->assertEquals(
            [['area' => 'adminhtml']],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
