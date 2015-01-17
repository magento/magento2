<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Parser;

use Magento\Tools\I18n\Parser as Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Parser\AbstractParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tools\I18n\FilesCollector
     */
    protected $filesCollector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tools\I18n\Factory
     */
    protected $factory;


    protected function setUp()
    {
        $this->filesCollector = $this->getMock('Magento\Tools\I18n\FilesCollector');
        $this->factory = $this->getMock('Magento\Tools\I18n\Factory');

        $this->parser = new Parser\Parser($this->filesCollector, $this->factory);
    }

    /**
     * @param array $options
     * @param array $phpFiles
     * @param array $jsFiles
     * @param array $phpMap
     * @param array $jsMap
     * @param array $expectedResult
     * @dataProvider addPhraseDataProvider
     */
    public function testAddPhrase($options, $phpFiles, $jsFiles, $phpMap, $jsMap, $expectedResult)
    {
        // 1. Create mocks
        $phpAdapter = new AdapterStub;
        $jsAdapter = new AdapterStub;

        // 2. Set mocks
        $this->parser->addAdapter('php', $phpAdapter);
        $this->parser->addAdapter('js', $jsAdapter);

        //3. Set fixtures
        $phpAdapter->setValueMap($phpMap);
        $jsAdapter->setValueMap($jsMap);

        $this->factory->expects($this->any())->method('createPhrase')->with()->will($this->returnValueMap([
            [['phrase' => 'php phrase111', 'translation' => 'php phrase111', 'quote' => "'"], 'php phrase111'],
            [['phrase' => 'php phrase112', 'translation' => 'php phrase112', 'quote' => '"'], 'php phrase112'],
            [['phrase' => 'php phrase121', 'translation' => 'php phrase121', 'quote' => "'"], 'php phrase121'],
            [['phrase' => 'php phrase122', 'translation' => 'php phrase122', 'quote' => '"'], 'php phrase122'],
            [['phrase' => 'js phrase111', 'translation' => 'js phrase111', 'quote' => "'"], 'js phrase111'],
            [['phrase' => 'js phrase112', 'translation' => 'js phrase112', 'quote' => '"'], 'js phrase112'],
            [['phrase' => 'js phrase121', 'translation' => 'js phrase121', 'quote' => "'"], 'js phrase121'],
            [['phrase' => 'js phrase122', 'translation' => 'js phrase122', 'quote' => '"'], 'js phrase122'],
        ]));

        //4. Set expectations
        $this->filesCollector->expects($this->any())
            ->method('getFiles')
            ->will($this->returnValueMap([
                [$options[0]['paths'], '', $phpFiles],
                [$options[1]['paths'], '', $jsFiles],
            ]));

        $result = $this->parser->parse($options);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function addPhraseDataProvider()
    {
        return [
            [
                [
                    ['type' => 'php', 'paths' => ['php/path/1', 'php/path/2']],
                    ['type' => 'js', 'paths' => ['js/path/1', 'js/path/2']],
                ],
                ['php/path1/file11', 'php/path1/file12', 'php/path2/file21'],
                ['js/path1/file11', 'js/path1/file12', 'js/path2/file21'],
                [
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
                [
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
                [
                    'php phrase111' => 'php phrase111',
                    'php phrase112' => 'php phrase112',
                    'php phrase121' => 'php phrase121',
                    'php phrase122' => 'php phrase122',
                    'js phrase111' => 'js phrase111',
                    'js phrase112' => 'js phrase112',
                    'js phrase121' => 'js phrase121',
                    'js phrase122' => 'js phrase122',
                ]
            ]
        ];
    }
}

class AdapterStub implements Parser\AdapterInterface
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
