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
                    ['synonyms' => 'queen,monarch', 'scope_id' => 1, 'scope_type' => 'stores'],
                    ['synonyms' => 'british,english', 'scope_id' => 1, 'scope_type' => 'stores'],
                    ['synonyms' => 'schlicht,natürlich', 'scope_id' => 1, 'scope_type' => 'stores']
                ]
            ],
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
        $this->assertEquals($expectedResult[0]['scope_id'], $data[0]['scope_id']);
        $this->assertEquals($expectedResult[0]['scope_type'], $data[0]['scope_type']);
        $this->assertEquals($expectedResult[1]['synonyms'], $data[1]['synonyms']);
        $this->assertEquals($expectedResult[1]['scope_id'], $data[1]['scope_id']);
        $this->assertEquals($expectedResult[1]['scope_type'], $data[1]['scope_type']);
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
