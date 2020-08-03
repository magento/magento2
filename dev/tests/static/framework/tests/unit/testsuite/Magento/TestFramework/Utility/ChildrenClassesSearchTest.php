<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

use Magento\Framework\App\Action\AbstractAction;

class ChildrenClassesSearchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChildrenClassesSearch
     */
    private $childrenClassesSearch;

    protected function setUp(): void
    {
        $this->childrenClassesSearch = new ChildrenClassesSearch();
    }

    public function testChildrenSearch(): void
    {
        $files = [
            __DIR__ . '/PartialNamespace/Foo.php',
            __DIR__ . '/PartialNamespace/Bar.php',
            __DIR__ . '/PartialNamespace/Baz.php',
        ];

        $found = $this->childrenClassesSearch->getClassesWhichAreChildrenOf(
            $files,
            AbstractAction::class,
            false
        );

        $this->assertCount(1, $found);
        $this->assertEquals(current($found), \Magento\TestFramework\Utility\PartialNamespace\Foo::class);
    }
}
