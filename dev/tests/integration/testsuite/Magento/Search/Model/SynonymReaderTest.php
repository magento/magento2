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
    public static function loadByStoreViewIdDataProvider()
    {
        return [
            [
                1,
                [
                    ['synonyms' => 'queen,monarch', 'store_id' => 1],
                    ['synonyms' => 'british,english', 'store_id' => 1]
                ]
            ],
            [
                0,
                [
                    ['synonyms' => 'universe,cosmos', 'store_id' => 0],
                    ['synonyms' => 'big,huge,large,enormous', 'store_id' => 0]
                ]
            ]
        ];
    }

    /**
     * @param int $storeviewId
     * @param array $expectedResult
     * @dataProvider loadByStoreViewIdDataProvider
     */
    public function testloadByStoreViewId($storeViewId, $expectedResult)
    {
        $data = $this->model->loadByStoreViewId($storeViewId)->getData();
        $this->assertEquals($expectedResult[0]['synonyms'], $data[0]['synonyms']);
        $this->assertEquals($expectedResult[0]['store_id'], $data[0]['store_id']);
        $this->assertEquals($expectedResult[1]['synonyms'], $data[1]['synonyms']);
        $this->assertEquals($expectedResult[1]['store_id'], $data[1]['store_id']);
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
                'ENGLISH', [['synonyms' => 'british,english', 'store_id' => 1]]
            ],
            [
                'English', [['synonyms' => 'british,english', 'store_id' => 1]]
            ],
            [
                'QUEEN', [['synonyms' => 'queen,monarch', 'store_id' => 1]]
            ],
            [
                'Monarch', [['synonyms' => 'queen,monarch', 'store_id' => 1]]
            ],
            [
                'MONARCH English', [
                ['synonyms' => 'queen,monarch', 'store_id' => 1],
                ['synonyms' => 'british,english', 'store_id' => 1]
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
            $this->assertEquals($r['store_id'], $data[$i]['store_id']);
            ++$i;
        }
    }
}
