<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts
 */
class SchemaReaderTest extends SetupTestCase
{
    /** @var  \Magento\Setup\Model\Declaration\Schema\FileSystem\Reader */
    private $reader;

    /** @var  TestModuleManager */
    private $moduleManager;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->reader = $objectManager->get(\Magento\Setup\Model\Declaration\Schema\FileSystem\Reader::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/valid_xml_revision_1.php
     */
    public function testSuccessfullRead()
    {
        $schema = $this->reader->read();
        self::assertEquals($schema, $this->getData());
    }

    /**
     * Helper method. Decrease number of params
     *
     * @param string $revisionName
     * @return void
     */
    private function updateRevisionTo($revisionName)
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            $revisionName,
            TestModuleManager::DECLARATIVE_FILE_NAME,
            'etc'
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessageRegExp /The attribute 'scale' is not allowed./
     * @moduleName Magento_TestSetupDeclarationModule1
     */
    public function testFailOnInvalidColumnDeclaration()
    {
        $this->updateRevisionTo('fail_on_column_declaration');
        $this->reader->read();
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/foreign_key_interpreter_result.php
     */
    public function testForeignKeyInterpreter()
    {
        $this->updateRevisionTo('foreign_key_interpreter');
        $schema = $this->reader->read();
        self::assertEquals($schema, $this->getData());
    }
}
