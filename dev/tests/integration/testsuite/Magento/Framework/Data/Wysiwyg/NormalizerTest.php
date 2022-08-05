<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Wysiwyg;

class NormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Wysiwyg\Normalizer
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Data\Wysiwyg\Normalizer::class
        );
    }

    public function testReplaceReservedCharacters()
    {
        $content = '{}\\""[]';
        $expected = '^[^]|``[]';
        $this->assertEquals($expected, $this->normalizer->replaceReservedCharacters($content));
    }

    public function testRestoreReservedCharacters()
    {
        $content = '^[^]|``[]';
        $expected = '{}\\""[]';
        $this->assertEquals($expected, $this->normalizer->restoreReservedCharacters($content));
    }

    public function testReplaceAndRestoreReservedCharacters()
    {
        $value = '{"1":{"type":"Magento\\CatalogWidget\\Model\\Rule\\Condition\\Combine",'
            . '"aggregator":"all","value":"1","new_child":""},"1--1":{"type":'
            . '"Magento\\CatalogWidget\\Model\\Rule\\Condition\\Product","attribute":"pattern",'
            . '"operator":"{}","value":["212,213"]}}';
        $this->assertEquals(
            $value,
            $this->normalizer->restoreReservedCharacters(
                $this->normalizer->replaceReservedCharacters($value)
            )
        );
    }
}
