<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Parser;

use Magento\TestFramework\Fixture\ComponentsDir;
use Magento\TestFramework\Fixture\ParserInterface;
use PHPUnit\Framework\TestCase;

#[
    ComponentsDir('path/to/folder1')
]
class ComponentsDirTest extends TestCase
{
    #[
        ComponentsDir('path/to/folder2')
    ]
    public function testScopeMethod(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\ComponentsDir();
        $this->assertEquals(
            [['path' => 'path/to/folder2']],
            $model->parse($this, ParserInterface::SCOPE_METHOD)
        );
    }

    #[
        ComponentsDir('path/to/folder3')
    ]
    public function testScopeClass(): void
    {
        $model = new \Magento\TestFramework\Fixture\Parser\ComponentsDir();
        $this->assertEquals(
            [['path' => 'path/to/folder1']],
            $model->parse($this, ParserInterface::SCOPE_CLASS)
        );
    }
}
