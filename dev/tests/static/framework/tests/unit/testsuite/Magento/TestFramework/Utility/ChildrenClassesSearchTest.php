<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Utility;

use Magento\TestFramework\Utility\ChildrenClassesSearch\A;
use Magento\TestFramework\Utility\ChildrenClassesSearch\B;
use Magento\TestFramework\Utility\ChildrenClassesSearch\E;
use Magento\TestFramework\Utility\ChildrenClassesSearch\F;
use PHPUnit\Framework\TestCase;

class ChildrenClassesSearchTest extends TestCase
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
            __DIR__ . '/ChildrenClassesSearch/A.php',
            __DIR__ . '/ChildrenClassesSearch/B.php',
            __DIR__ . '/ChildrenClassesSearch/C.php',
            __DIR__ . '/ChildrenClassesSearch/D.php',
            __DIR__ . '/ChildrenClassesSearch/E.php',
            __DIR__ . '/ChildrenClassesSearch/F.php',
            __DIR__ . '/ChildrenClassesSearch/ZInterface.php',
        ];

        $found = $this->childrenClassesSearch->getClassesWhichAreChildrenOf(
            $files,
            A::class,
            false
        );

        $expected = [
            B::class,
            E::class,
            F::class
        ];

        $this->assertSame($expected, $found);
    }
}
