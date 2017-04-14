<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget\Wysiwyg;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Widget\Wysiwyg\Normalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget\Wysiwyg\Normalizer::class
        );
    }

    public function testReplaceReservedCharaters()
    {
        $content = '{}\\""';
        $expected = '[]|``';
        $this->assertEquals($expected, $this->normalizer->replaceReservedCharaters($content));
    }

    public function testRestoreReservedCharaters()
    {
        $content = '[]|``';
        $expected = '{}\\""';
        $this->assertEquals($expected, $this->normalizer->restoreReservedCharaters($content));
    }
}
