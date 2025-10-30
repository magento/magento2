<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser;

use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\FilesCollector;
use Magento\Setup\Module\I18n\Parser\AbstractParser;
use Magento\Setup\Module\I18n\Parser\AdapterInterface;
use Magento\Setup\Module\I18n\Parser as Parser;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var AbstractParser|MockObject
     */
    protected $parser;

    /**
     * @var MockObject|FilesCollector
     */
    protected $filesCollector;

    /**
     * @var MockObject|Factory
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->filesCollector = $this->createMock(FilesCollector::class);
        $this->factory = $this->createMock(Factory::class);

        $this->parser = new Parser\Parser($this->filesCollector, $this->factory);
    }

    /**
     * @param array $options
     * @param array $phpFiles
     * @param array $jsFiles
     * @param array $phpMap
     * @param array $jsMap
     * @param array $phraseFactoryMap
     * @param array $expectedResult
     * @dataProvider addPhraseDataProvider
     */
    public function testAddPhrase($options, $phpFiles, $jsFiles, $phpMap, $jsMap, $phraseFactoryMap, $expectedResult)
    {
        foreach ($phraseFactoryMap as &$phrase) {
            if (is_callable($phrase[1])) {
                $phrase[1] = $phrase[1]($this);
            }
        }

        $finalExpectedResult = [];
        foreach ($expectedResult as $key => $result) {
            if (is_callable($result)) {
                $finalExpectedResult[$key] = $result($this);
            }
        }

        // 1. Create mocks
        $phpAdapter = new AdapterStub();
        $jsAdapter = new AdapterStub();

        // 2. Set mocks
        $this->parser->addAdapter('php', $phpAdapter);
        $this->parser->addAdapter('js', $jsAdapter);

        //3. Set fixtures
        $phpAdapter->setValueMap($phpMap);
        $jsAdapter->setValueMap($jsMap);

        $this->factory->expects($this->any())
            ->method('createPhrase')
            ->willReturnMap($phraseFactoryMap);

        //4. Set expectations
        $this->filesCollector->expects($this->any())
            ->method('getFiles')
            ->willReturnMap([
                [$options[0]['paths'], '', $phpFiles],
                [$options[1]['paths'], '', $jsFiles],
            ]);

        $result = $this->parser->parse($options);
        $this->assertEquals($finalExpectedResult, $result);
    }

    /**
     * @return array
     */
    public static function addPhraseDataProvider(): array
    {
        $phraseMock1 = static fn (self $testCase) => $testCase->createPhaseMock('php phrase111');
        $phraseMock2 = static fn (self $testCase) => $testCase->createPhaseMock('php phrase112');
        $phraseMock3 = static fn (self $testCase) => $testCase->createPhaseMock('php phrase121');
        $phraseMock4 = static fn (self $testCase) => $testCase->createPhaseMock('php phrase122');
        $phraseMock5 = static fn (self $testCase) => $testCase->createPhaseMock('js phrase111');
        $phraseMock6 = static fn (self $testCase) => $testCase->createPhaseMock('js phrase112');
        $phraseMock7 = static fn (self $testCase) => $testCase->createPhaseMock('js phrase121');
        $phraseMock8 = static fn (self $testCase) => $testCase->createPhaseMock('js phrase122');

        return [
            [
                'options' => [
                    ['type' => 'php', 'paths' => ['php/path/1', 'php/path/2']],
                    ['type' => 'js', 'paths' => ['js/path/1', 'js/path/2']],
                ],
                'phpFiles' => ['php/path1/file11', 'php/path1/file12', 'php/path2/file21'],
                'jsFiles' => ['js/path1/file11', 'js/path1/file12', 'js/path2/file21'],
                'phpMap' => [
                    'php/path1/file11' => [
                        [
                            'phrase' => 'php phrase111',
                            'quote' => "'"
                        ],
                        [   'phrase' => 'php phrase112',
                            'quote' => '"'
                        ]
                    ],
                    'php/path1/file12' => [
                        [
                            'phrase' => 'php phrase121',
                            'quote' => "'"
                        ],
                        [   'phrase' => 'php phrase122',
                            'quote' => '"'
                        ]
                    ],
                    'php/path2/file21' => []
                ],
                'jsMap' => [
                    'js/path1/file11' => [
                        [
                            'phrase' => 'js phrase111',
                            'quote' => "'"
                        ],
                        [   'phrase' => 'js phrase112',
                            'quote' => '"'
                        ]
                    ],
                    'js/path1/file12' => [
                        [
                            'phrase' => 'js phrase121',
                            'quote' => "'"
                        ],
                        [   'phrase' => 'js phrase122',
                            'quote' => '"'
                        ]
                    ],
                    'js/path2/file21' => []
                ],
                'phraseFactoryMap' => [
                    [['phrase' => 'php phrase111', 'translation' => 'php phrase111', 'quote' => "'"], $phraseMock1],
                    [['phrase' => 'php phrase112', 'translation' => 'php phrase112', 'quote' => '"'], $phraseMock2],
                    [['phrase' => 'php phrase121', 'translation' => 'php phrase121', 'quote' => "'"], $phraseMock3],
                    [['phrase' => 'php phrase122', 'translation' => 'php phrase122', 'quote' => '"'], $phraseMock4],
                    [['phrase' => 'js phrase111', 'translation' => 'js phrase111', 'quote' => "'"], $phraseMock5],
                    [['phrase' => 'js phrase112', 'translation' => 'js phrase112', 'quote' => '"'], $phraseMock6],
                    [['phrase' => 'js phrase121', 'translation' => 'js phrase121', 'quote' => "'"], $phraseMock7],
                    [['phrase' => 'js phrase122', 'translation' => 'js phrase122', 'quote' => '"'], $phraseMock8],
                ],
                'expectedResult' => [
                    'php phrase111' => $phraseMock1,
                    'php phrase112' => $phraseMock2,
                    'php phrase121' => $phraseMock3,
                    'php phrase122' => $phraseMock4,
                    'js phrase111' => $phraseMock5,
                    'js phrase112' => $phraseMock6,
                    'js phrase121' => $phraseMock7,
                    'js phrase122' => $phraseMock8,
                ],
            ]
        ];
    }

    /**
     * @param string $result
     * @return MockObject
     * @throws Exception
     */
    public function createPhaseMock(string $result): MockObject
    {
        $phraseMock = $this->createMock(Phrase::class);
        $phraseMock->expects($this->any())->method('getCompiledPhrase')->willReturn($result);
        return $phraseMock;
    }
}

// @codingStandardsIgnoreStart
class AdapterStub implements AdapterInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $map = [];

    /**
     * {@inheritdoc}
     */
    public function parse($file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhrases()
    {
        return $this->map[$this->file];
    }

    /**
     * @param array $map
     */
    public function setValueMap($map)
    {
        $this->map = $map;
    }
}
