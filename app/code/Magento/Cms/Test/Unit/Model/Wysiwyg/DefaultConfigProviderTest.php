<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Wysiwyg;

use Magento\Cms\Model\Wysiwyg\DefaultConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultConfigProviderTest extends TestCase
{
    /**
     * @var Repository|MockObject
     */
    private Repository $assetRepo;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->assetRepo = $this->createMock(Repository::class);
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetConfig(): void
    {
        $config = new DataObject();
        $configProvider = new DefaultConfigProvider($this->assetRepo);
        $result = $configProvider->getConfig($config);
        $this->assertStringContainsString('fontfamily fontsizeinput', $result->getTinymce()['toolbar']);
    }
}
