<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Search/_files/synonym_reader.php
 */
class SynonymReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymReader
     */
    private $model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->get('Magento\Search\Model\SynonymReader');
    }

    /**
     * @return array
     */
    public static function loadByPhraseDataProvider()
    {
        return [
            [
                'ELIZABETH', []
            ],
            [
                'ENGLISH', [['synonyms' => 'british,english', 'scope_id' => 1, 'scope_type' => 'stores']]
            ],
            [
                'English', [['synonyms' => 'british,english', 'scope_id' => 1, 'scope_type' => 'stores']]
            ],
            [
                'QUEEN', [['synonyms' => 'queen,monarch', 'scope_id' => 1, 'scope_type' => 'stores']]
            ],
            [
                'Monarch', [['synonyms' => 'queen,monarch', 'scope_id' => 1, 'scope_type' => 'stores']]
            ],
            [
                'MONARCH English', [
                ['synonyms' => 'queen,monarch', 'scope_id' => 1, 'scope_type' => 'stores'],
                ['synonyms' => 'british,english', 'scope_id' => 1, 'scope_type' => 'stores']
            ]
            ]
        ];
    }

    /**
     * @param string $phrase
     * @param array $expectedResult
     * @dataProvider loadByPhraseDataProvider
     */
    public function testLoadByPhrase($phrase, $expectedResult)
    {
        $data = $this->model->loadByPhrase($phrase)->getData();

        $i = 0;
        foreach ($expectedResult as $r) {
            $this->assertEquals($r['synonyms'], $data[$i]['synonyms']);
            $this->assertEquals($r['scope_id'], $data[$i]['scope_id']);
            $this->assertEquals($r['scope_type'], $data[$i]['scope_type']);
            ++$i;
        }
    }
}
