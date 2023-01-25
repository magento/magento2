<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * Test for Upgrade command.
 */
class SetupUpgradeTest extends SetupTestCase
{
    /**
     * @var TestModuleManager
     */
    private $moduleManager;

    /**
     * @var CliCommand
     */
    private $cliCommand;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->componentRegistrar = $objectManager->create(
            ComponentRegistrar::class
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule8
     * @moduleName Magento_TestSetupDeclarationModule9
     * @throws \Exception
     */
    public function testUpgradeWithConverting()
    {
        $modules = [
            'Magento_TestSetupDeclarationModule8',
            'Magento_TestSetupDeclarationModule9',
        ];

        foreach ($modules as $moduleName) {
            $this->moduleManager->updateRevision(
                $moduleName,
                'setup_install_with_converting',
                'InstallSchema.php',
                'Setup'
            );
        }

        $this->cliCommand->install($modules, ['convert-old-scripts' => true]);
        foreach ($modules as $moduleName) {
            $this->assertInstallScriptChanges($moduleName);
        }

        foreach ($modules as $moduleName) {
            $this->moduleManager->updateRevision(
                $moduleName,
                'setup_install_with_converting',
                'UpgradeSchema.php',
                'Setup'
            );

            $this->moduleManager->updateRevision(
                $moduleName,
                'setup_install_with_converting',
                'module.xml',
                'etc'
            );
        }

        $this->cliCommand->upgrade(['convert-old-scripts' => true]);

        foreach ($modules as $moduleName) {
            $this->assertUpgradeScriptChanges($moduleName);
        }
    }

    /**
     * Convert file content in the DOM document.
     *
     * @param string $schemaFileName
     * @return \DOMDocument
     */
    private function getSchemaDocument(string $schemaFileName): \DOMDocument
    {
        $schemaDocument = new \DOMDocument();
        $schemaDocument->preserveWhiteSpace = false;
        $schemaDocument->formatOutput = true;
        $schemaDocument->loadXML(file_get_contents($schemaFileName));

        return $schemaDocument;
    }

    /**
     * @param string $moduleName
     */
    private function assertInstallScriptChanges(string $moduleName): void
    {
        $generatedSchema = $this->getGeneratedSchema($moduleName);
        $expectedSchema = $this->getSchemaDocument($this->getSchemaFixturePath($moduleName, 'install'));

        $this->assertEquals($expectedSchema->saveXML(), $generatedSchema->saveXML());
    }

    /**
     * @param string $moduleName
     */
    private function assertUpgradeScriptChanges(string $moduleName): void
    {
        $generatedSchema = $this->getGeneratedSchema($moduleName);
        $expectedSchema = $this->getSchemaDocument($this->getSchemaFixturePath($moduleName, 'upgrade'));

        $this->assertEquals($expectedSchema->saveXML(), $generatedSchema->saveXML());
    }

    /**
     * @param string $moduleName
     * @return \DOMDocument
     */
    private function getGeneratedSchema(string $moduleName): \DOMDocument
    {
        $modulePath = $this->componentRegistrar->getPath('module', $moduleName);
        $schemaFileName = $modulePath
            . DIRECTORY_SEPARATOR
            . \Magento\Framework\Module\Dir::MODULE_ETC_DIR
            . DIRECTORY_SEPARATOR
            . 'db_schema.xml';

        return $this->getSchemaDocument($schemaFileName);
    }

    /**
     * @param string $moduleName
     * @param string $suffix
     * @return string
     */
    private function getSchemaFixturePath(string $moduleName, string $suffix): string
    {
        $schemaFixturePath = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . implode(
                DIRECTORY_SEPARATOR,
                [
                    '_files',
                    'SetupUpgrade',
                    str_replace('Magento_', '', $moduleName),
                    'db_schema_' . $suffix . '.xml'
                ]
            );

        return $schemaFixturePath;
    }
}
