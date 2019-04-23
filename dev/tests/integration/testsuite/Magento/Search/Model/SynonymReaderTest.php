<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
                '-+<(ELIZABETH)>*~', []
            ],
            [
                'ENGLISH', [['synonyms' => 'british,english', 'store_id' => 1, 'website_id' => 0]]
            ],
            [
                'English', [['synonyms' => 'british,english', 'store_id' => 1, 'website_id' => 0]]
            ],
            [
                'QUEEN', [['synonyms' => 'queen,monarch', 'store_id' => 1, 'website_id' => 0]]
            ],
            [
                'Monarch', [['synonyms' => 'queen,monarch', 'store_id' => 1, 'website_id' => 0]]
            ],
            [
                '-+<(Monarch)>*~', [['synonyms' => 'queen,monarch', 'store_id' => 1, 'website_id' => 0]]
            ],
            [
                'MONARCH English', [
                ['synonyms' => 'queen,monarch', 'store_id' => 1, 'website_id' => 0],
                ['synonyms' => 'british,english', 'store_id' => 1, 'website_id' => 0]
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
            $this->assertEquals($r['website_id'], $data[$i]['website_id']);
            ++$i;
        }
    }
}
