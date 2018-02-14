<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsSingleSourceModeTest extends TestCase
{
    /**
     * @var IsSingleSourceModeInterface
     */
    protected $isSingleSourcekMode;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->isSingleSourcekMode = Bootstrap::getObjectManager()->get(IsSingleSourceModeInterface::class);
        $this->sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
    }

    public function testExecuteOnCleanInstall()
    {
        self::assertTrue($this->isSingleSourcekMode->execute());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testExecuteWithTwoSourcesOneDisabled()
    {
        $sourceToDisable = $this->sourceRepository->get('source-code-1');
        $sourceToDisable->setEnabled(false);
        $this->sourceRepository->save($sourceToDisable);

        self::assertTrue($this->isSingleSourcekMode->execute());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testExecuteWithEnabledSources()
    {
        self::assertFalse($this->isSingleSourcekMode->execute());
    }
}
