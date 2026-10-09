<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 */
class ImporterTest extends TestCase
{
    /**
     * @var State
     */
    private $appState;

    protected function setUp(): void
    {
        $this->appState = Bootstrap::getObjectManager()->get(State::class);
    }

    public function testGetWarningMessagesDoesNotMislabelFrontendThemesAsAdminhtmlWhileAreaIsEmulated(): void
    {
        $messages = $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            function () {
                $importer = Bootstrap::getObjectManager()->create(Importer::class);
                return $importer->getWarningMessages([]);
            }
        );

        $joinedMessages = implode(PHP_EOL, $messages);

        $this->assertStringNotContainsString('adminhtml/Magento/blank', $joinedMessages);
        $this->assertStringNotContainsString('adminhtml/Magento/luma', $joinedMessages);
    }

    public function testGetWarningMessagesForCompleteThemesDoesNotMisreportFilesystemThemesWhileAreaIsEmulated(): void
    {
        $completeThemes = [
            'adminhtml/Magento/backend' => ['area' => 'adminhtml'],
            'frontend/Magento/blank' => ['area' => 'frontend'],
            'frontend/Magento/luma' => ['area' => 'frontend'],
        ];

        $messages = $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            function () use ($completeThemes) {
                $importer = Bootstrap::getObjectManager()->create(Importer::class);
                return $importer->getWarningMessages($completeThemes);
            }
        );

        $joinedMessages = implode(PHP_EOL, $messages);

        $this->assertStringNotContainsString('adminhtml/Magento/blank', $joinedMessages);
        $this->assertStringNotContainsString('adminhtml/Magento/luma', $joinedMessages);

        $removalMessages = implode(
            PHP_EOL,
            array_filter($messages, static fn (string $message): bool => str_contains($message, 'will be removed'))
        );

        $this->assertStringNotContainsString('frontend/Magento/blank', $removalMessages);
        $this->assertStringNotContainsString('frontend/Magento/luma', $removalMessages);
        $this->assertStringNotContainsString('adminhtml/Magento/backend', $removalMessages);
    }
}
