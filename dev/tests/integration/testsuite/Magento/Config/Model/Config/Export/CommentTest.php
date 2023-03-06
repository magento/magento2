<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Export;

use Magento\Config\Model\Config\TypePool;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    /**
     * @var Comment
     */
    private $comment;

    protected function setUp(): void
    {
        $this->comment = Bootstrap::getObjectManager()->create(Comment::class);
    }

    public function testGet()
    {
        $sensitivePaths = $this->getSensitivePaths();
        $comments = $this->comment->get();

        $missedPaths = [];
        foreach ($sensitivePaths as $sensitivePath) {
            if (stripos($comments, $sensitivePath) === false) {
                $missedPaths[] = $sensitivePath;
            }
        }

        $this->assertEmpty(
            $missedPaths,
            'Sensitive paths are missed: ' . implode(', ', $missedPaths)
        );
    }

    /**
     * Retrieve sensitive paths from class that is used to check is path sensitive.
     *
     * There is no public method to get this data.
     * It's why they are read using private method.
     *
     * @return array
     */
    private function getSensitivePaths(): array
    {
        $typePool = Bootstrap::getObjectManager()->get(TypePool::class);
        $sensitivePathsReader = \Closure::bind(
            function () {
                return $this->getPathsByType(TypePool::TYPE_SENSITIVE);
            },
            $typePool,
            $typePool
        );

        return $sensitivePathsReader();
    }
}
