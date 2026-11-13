<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\Dom;
use Magento\Framework\Setup\Declaration\Schema\Config\Converter;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregator;
use Magento\Framework\Setup\Declaration\Schema\Declaration\SchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\Operations\CreateTable;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify negative defaults survive validation and declarative table creation.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegerDefaultTest extends TestCase
{
    /**
     * Create a table from a validated declaration and use its database default.
     */
    #[DbIsolation(false)]
    public function testNegativeDefaultReachesDatabase(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $resource = $objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('declarative_integer_defaults');
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <table name="declarative_integer_defaults" resource="default" engine="innodb" comment="Integer defaults">
        <column xsi:type="int" name="some_name" unsigned="false" nullable="false"
                identity="false" default="-1" comment="Some Name"/>
    </table>
</schema>
XML);
        self::assertSame(
            [],
            Dom::validateDomDocument($document, 'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd')
        );

        $data = $objectManager->get(Converter::class)->convert($document);
        $schema = $objectManager->create(SchemaBuilder::class)
            ->addTablesData($data['table'])
            ->build($objectManager->create(Schema::class));
        $table = $schema->getTableByName($tableName);
        self::assertInstanceOf(Table::class, $table);
        $statements = new StatementAggregator();
        $statements->addStatements(
            $objectManager->get(CreateTable::class)->doOperation(new ElementHistory($table))
        );

        $connection->dropTable($tableName);
        try {
            $objectManager->get(DbSchemaWriterInterface::class)->compile($statements, false);
            $columns = $connection->describeTable($tableName);
            self::assertSame('-1', (string)$columns['some_name']['DEFAULT']);
            $connection->query('INSERT INTO ' . $connection->quoteIdentifier($tableName) . ' () VALUES ()');
            $value = $connection->fetchOne($connection->select()->from($tableName, ['some_name']));
            self::assertSame(-1, (int)$value);
        } finally {
            $connection->dropTable($tableName);
        }
    }
}
