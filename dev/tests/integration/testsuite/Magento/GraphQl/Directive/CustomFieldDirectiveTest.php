<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directive;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Custom GraphQL field directives declared by a module through di.xml only.
 *
 * The directive "testScope" is declared by Magento_TestModuleGraphQlDirective:
 * - etc/di.xml registers a DirectiveReader virtual type under the "test_scope" key of FieldMetaReader;
 * - etc/schema.graphqls uses it, repeated, on the Query.testDirectiveScopes field.
 *
 * @magentoAppArea graphql
 */
class CustomFieldDirectiveTest extends TestCase
{
    private const EXPECTED_DIRECTIVES = [
        'test_scope' => [
            ['name' => 'catalog'],
            ['name' => 'customer', 'tags' => ['read', 'write']],
        ],
    ];

    /**
     * The directives read by the registered reader are exposed by the Field config element
     *
     * @return void
     */
    public function testDirectivesAreExposedByFieldConfig(): void
    {
        $this->assertSame(self::EXPECTED_DIRECTIVES, $this->getQueryField('testDirectiveScopes')->getDirectives());
    }

    /**
     * A field without the directive has no directives, the other readers' result is not polluted
     *
     * @return void
     */
    public function testFieldWithoutDirectiveHasNoDirectives(): void
    {
        $field = $this->getQueryField('testDirectiveWithoutScope');

        $this->assertSame([], $field->getDirectives());
        $this->assertSame([], $this->getQueryField('products')->getDirectives());
    }

    /**
     * Built-in annotations are still read when custom directives are present on the same field
     *
     * @return void
     */
    public function testBuiltInAnnotationsAreStillRead(): void
    {
        $field = $this->getQueryField('testDirectiveScopes');

        $this->assertSame('Return the testScope directives declared on this field.', $field->getDescription());
        $this->assertSame(['cacheable' => false], $field->getCache());
        $this->assertSame(
            'Magento\TestModuleGraphQlDirective\Model\Resolver\DirectiveScopes',
            $field->getResolver()
        );
    }

    /**
     * The directives reach the resolver through the Field it receives, end to end through the /graphql controller
     *
     * @return void
     */
    public function testDirectivesAreAvailableInResolver(): void
    {
        $query = <<<'QUERY'
{
    testDirectiveScopes { name tags }
    testDirectiveWithoutScope { name tags }
}
QUERY;

        $objectManager = Bootstrap::getObjectManager();
        $response = $objectManager->get(GraphQlRequest::class)->send($query);
        $result = $objectManager->get(SerializerInterface::class)->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $result, $response->getContent());
        $this->assertSame(
            [
                ['name' => 'catalog', 'tags' => []],
                ['name' => 'customer', 'tags' => ['read', 'write']],
            ],
            $result['data']['testDirectiveScopes']
        );
        $this->assertSame([], $result['data']['testDirectiveWithoutScope']);
    }

    /**
     * Get a field of the Query type from the stitched GraphQL config
     *
     * @param string $fieldName
     * @return Field
     */
    private function getQueryField(string $fieldName): Field
    {
        /** @var Type $query */
        $query = Bootstrap::getObjectManager()->get(ConfigInterface::class)->getConfigElement('Query');
        $fields = $query->getFields();
        $this->assertArrayHasKey($fieldName, $fields);

        return $fields[$fieldName];
    }
}
