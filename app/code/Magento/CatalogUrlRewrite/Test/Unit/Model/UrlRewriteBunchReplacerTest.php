<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\UrlRewrite\Model\UrlPersistInterface;

class UrlRewriteBunchReplacerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrlPersistInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlPersistMock;

    /**
     * @var UrlRewriteBunchReplacer
     */
    private $urlRewriteBunchReplacer;

    protected function setUp(): void
    {
        $this->urlPersistMock = $this->getMockForAbstractClass(UrlPersistInterface::class);
        $this->urlRewriteBunchReplacer = new UrlRewriteBunchReplacer(
            $this->urlPersistMock
        );
    }

    public function testDoBunchReplace()
    {
        $urls = [[1], [2]];
        $this->urlPersistMock->expects($this->exactly(2))
            ->method('replace')
            ->withConsecutive([[[1]]], [[[2]]]);
        $this->urlRewriteBunchReplacer->doBunchReplace($urls, 1);
    }
}
