<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector
 */
class MethodCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodCollector
     */
    protected $methodCollector;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->methodCollector = $this->objectManager->create(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector::class
        );
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector::parse
     */
    public function testParse()
    {
        $file = __DIR__.'/../_files/methodsCode.php.txt';
        $this->methodCollector->parse($file);
        $expectation = [
            [
                'phrase' => '\'Some string\'',
                'arguments' => 0,
                'file' => $file,
                'line' => 4
            ],
            [
                'phrase' => '\'One more string\'',
                'arguments' => 0,
                'file' => $file,
                'line' => 5
            ]
        ];
        $this->assertEquals($expectation, $this->methodCollector->getPhrases());
    }
}
