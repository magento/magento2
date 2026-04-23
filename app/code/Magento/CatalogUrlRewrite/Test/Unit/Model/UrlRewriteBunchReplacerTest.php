<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlRewriteBunchReplacerTest extends TestCase
{
    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersistMock;

    /**
     * @var UrlRewriteBunchReplacer
     */
    private $urlRewriteBunchReplacer;

    protected function setUp(): void
    {
        $this->urlPersistMock = $this->createMock(UrlPersistInterface::class);
        $this->urlRewriteBunchReplacer = new UrlRewriteBunchReplacer(
            $this->urlPersistMock
        );
    }

    public function testDoBunchReplace()
    {
        $urls = [[1], [2]];
        $this->urlPersistMock->expects($this->exactly(2))
            ->method('replace')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == [[1]] || $arg1 == [[1]]) {
                    return null;
                }
            });
        $this->urlRewriteBunchReplacer->doBunchReplace($urls, 1);
    }
}
