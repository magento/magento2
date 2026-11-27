<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Wysiwyg;

use DOMDocument;
use DOMXPath;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\ConfigurableWYSIWYGValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the default HTML tag allowlists independently of DI configuration merging.
 */
class DefaultWysiwygValidatorTest extends TestCase
{
    /**
     * All six heading levels must pass validation with either default allowlist.
     */
    #[DataProvider('configurationProvider')]
    public function testAllowsAllHeadingLevels(string $configFile): void
    {
        $validator = $this->createConfiguredValidator($configFile);
        $validator->validate(
            '<div><h1>Heading 1</h1><h2>Heading 2</h2><h3>Heading 3</h3>'
            . '<h4>Heading 4</h4><h5>Heading 5</h5><h6>Heading 6</h6></div>'
        );
    }

    /**
     * Allowing headings must not allow unrelated restricted tags.
     */
    #[DataProvider('configurationProvider')]
    public function testRejectsRestrictedTag(string $configFile): void
    {
        $validator = $this->createConfiguredValidator($configFile);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Allowed HTML tags are:');
        $validator->validate('<iframe></iframe>');
    }

    /**
     * Provides each configuration separately so merging cannot hide an omitted tag.
     *
     * @return array<string, array{string}>
     */
    public static function configurationProvider(): array
    {
        return [
            'global' => [BP . '/app/etc/di.xml'],
            'cms' => [__DIR__ . '/../../../../etc/di.xml']
        ];
    }

    /**
     * Builds the validator using the tag allowlist from the production configuration.
     */
    private function createConfiguredValidator(string $configFile): ConfigurableWYSIWYGValidator
    {
        $document = new DOMDocument();
        $this->assertTrue($document->load($configFile));
        $xpath = new DOMXPath($document);
        $nodes = $xpath->query(
            '/config/virtualType[@name="DefaultWYSIWYGValidator"]'
            . '/arguments/argument[@name="allowedTags"]/item'
        );
        $this->assertNotFalse($nodes);

        $allowedTags = [];
        foreach ($nodes as $node) {
            $allowedTags[] = $node->textContent;
        }

        return new ConfigurableWYSIWYGValidator($allowedTags);
    }
}
