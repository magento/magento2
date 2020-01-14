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
 * Test for Install command.
 */
class SetupInstallTest extends SetupTestCase
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
    public function setUp()
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
    public function testInstallWithConverting()
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

        $this->cliCommand->install($modules, ['convert-old-scripts' => true]);

        foreach ($modules as $moduleName) {
            $modulePath = $this->componentRegistrar->getPath('module', $moduleName);
            $schemaFileName = $modulePath
                . DIRECTORY_SEPARATOR
                . \Magento\Framework\Module\Dir::MODULE_ETC_DIR
                . DIRECTORY_SEPARATOR
                . 'db_schema.xml';
            $generatedSchema = $this->getSchemaDocument($schemaFileName);

            $expectedSchemaFileName = dirname(__DIR__, 2)
                . DIRECTORY_SEPARATOR
                . implode(
                    DIRECTORY_SEPARATOR,
                    [
                        '_files',
                        'SetupInstall',
                        str_replace('Magento_', '', $moduleName),
                        'db_schema.xml'
                    ]
                );
            $expectedSchema = $this->getSchemaDocument($expectedSchemaFileName);

            $this->assertEquals($expectedSchema->saveXML(), $generatedSchema->saveXML());
        }
    }

    /**
     * Convert file content in the DOM document.
     *
     * @param $schemaFileName
     * @return \DOMDocument
     */
    private function getSchemaDocument($schemaFileName): \DOMDocument
    {
        $schemaDocument = new \DOMDocument();
        $schemaDocument->preserveWhiteSpace = false;
        $schemaDocument->formatOutput = true;
        $schemaDocument->loadXML(file_get_contents($schemaFileName));

        return $schemaDocument;
    }
}
