<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\Dom;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Declaration\Schema\Config\Converter;
use Magento\Framework\Setup\Declaration\Schema\Db\SchemaBuilder as DbSchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Declaration\SchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Exercise restrictive foreign keys through declarative installation and upgrade.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[DbIsolation(false)]
class RestrictForeignKeyTest extends TestCase
{
    /**
     * Restrictive foreign keys reject parent deletion and survive a second upgrade unchanged.
     *
     * @return void
     */
    public function testRestrictForeignKeyRoundTrip(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $resource = $objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $parent = $resource->getTableName('declarative_restrict_parent');
        $child = $resource->getTableName('declarative_restrict_child');
        $declaration = $this->getDeclaration($resource);
        $schemaDiff = $objectManager->get(SchemaDiff::class);
        $executor = $objectManager->get(OperationsExecutor::class);

        $connection->dropTable($child);
        $connection->dropTable($parent);

        try {
            $diff = $schemaDiff->diff($declaration, new Schema($resource));
            $executor->execute($diff, []);
            self::assertSame(Table::ACTION_RESTRICT, $connection->fetchOne(
                $connection->select()
                    ->from('information_schema.REFERENTIAL_CONSTRAINTS', ['DELETE_RULE'])
                    ->where('CONSTRAINT_SCHEMA = ?', $resource->getSchemaName('default'))
                    ->where('TABLE_NAME = ?', $child)
            ));
            $connection->insert($parent, ['id' => 1]);
            $connection->insert($child, ['id' => 1, 'parent_id' => 1]);
            $this->assertParentDeletionRejected($connection, $parent);

            $database = $objectManager->create(DbSchemaBuilder::class)->build(new Schema($resource));
            $actual = new Schema($resource);
            $actual->addTable($database->getTableByName($parent));
            $actual->addTable($database->getTableByName($child));
            $upgrade = $schemaDiff->diff($declaration, $actual);
            self::assertEmpty($upgrade->getAll(), 'Second upgrade must not recreate the foreign key.');
            $executor->execute($upgrade, []);
            $this->assertParentDeletionRejected($connection, $parent);
        } finally {
            $connection->dropTable($child);
            $connection->dropTable($parent);
        }
    }

    /**
     * Build a validated declaration without installing a fixture module.
     *
     * @param ResourceConnection $resource
     * @return Schema
     */
    private function getDeclaration(ResourceConnection $resource): Schema
    {
        $xml = <<<'XML'
<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <table name="declarative_restrict_parent" resource="default" engine="innodb">
        <column xsi:type="int" name="id" unsigned="true" nullable="false"/>
        <constraint xsi:type="primary" referenceId="PRIMARY"><column name="id"/></constraint>
    </table>
    <table name="declarative_restrict_child" resource="default" engine="innodb">
        <column xsi:type="int" name="id" unsigned="true" nullable="false"/>
        <column xsi:type="int" name="parent_id" unsigned="true" nullable="false"/>
        <constraint xsi:type="primary" referenceId="PRIMARY"><column name="id"/></constraint>
        <index referenceId="CHILD_PARENT_ID" indexType="btree"><column name="parent_id"/></index>
        <constraint xsi:type="foreign" referenceId="CHILD_PARENT_ID_PARENT_ID"
                    table="declarative_restrict_child" column="parent_id"
                    referenceTable="declarative_restrict_parent" referenceColumn="id" onDelete="RESTRICT"/>
    </table>
</schema>
XML;
        $document = new \DOMDocument();
        $document->loadXML($xml);
        self::assertSame([], Dom::validateDomDocument(
            $document,
            'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd'
        ));
        $data = (new Converter())->convert($document);
        $builder = Bootstrap::getObjectManager()->create(SchemaBuilder::class);
        $builder->addTablesData($data['table']);

        return $builder->build(new Schema($resource));
    }

    /**
     * Confirm that the database enforces the parent-child relationship.
     *
     * @param AdapterInterface $connection
     * @param string $parent
     * @return void
     */
    private function assertParentDeletionRejected(AdapterInterface $connection, string $parent): void
    {
        try {
            $connection->delete($parent, ['id = ?' => 1]);
            self::fail('Deleting a referenced parent must fail.');
        } catch (\Zend_Db_Statement_Exception $exception) {
            $previous = $exception->getPrevious();
            self::assertInstanceOf(\PDOException::class, $previous);
            self::assertSame(1451, (int) $previous->errorInfo[1]);
        }
        self::assertSame('1', (string) $connection->fetchOne(
            $connection->select()->from($parent, ['id'])->where('id = ?', 1)
        ));
    }
}
