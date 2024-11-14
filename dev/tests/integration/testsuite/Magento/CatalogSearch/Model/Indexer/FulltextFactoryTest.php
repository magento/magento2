<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class FulltextFactoryTest extends TestCase
{
    /**
     * @var FulltextFactory|null
     */
    private ?FulltextFactory $fulltextFactory;

    protected function setUp(): void
    {
        $this->fulltextFactory = Bootstrap::getObjectManager()->get(FulltextFactory::class);
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(Fulltext::class, $this->fulltextFactory->create());
    }
}
