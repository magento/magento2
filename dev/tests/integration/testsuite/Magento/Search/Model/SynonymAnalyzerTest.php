<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @magentoDataFixture Magento/Search/_files/synonym_reader.php
 * @magentoDbIsolation disabled
 */
class SynonymAnalyzerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymAnalyzer
     */
    private $synAnalyzer;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->synAnalyzer = $objectManager->get('Magento\Search\Model\SynonymAnalyzer');
    }

    /**
     * Data provider for the test
     *
     * @return array
     */
    public static function loadGetSynonymsForPhraseDataProvider()
    {
        return [
            'WithSynonymsFromStoreViewScope' => [
                'phrase' => 'elizabeth is the english queen',
                'expectedResult' => [['elizabeth'],['is'],['the'],['british', 'english'],['queen', 'monarch']]
            ],
            'WithSynonymsFromWebsiteScope' => [
                'phrase' => 'orange hill',
                'expectedResult' => [['orange', 'magento'], ['hill', 'mountain', 'peak']]
            ],
            'WithSynonymsFromDefaultScope' => [
                'phrase' => 'universe is enormous',
                'expectedResult' => [['universe', 'cosmos'], ['is'], ['big', 'huge', 'large', 'enormous']]
            ],
            'noSynonyms' => [
                'phrase' => 'this sentence has no synonyms',
                'expectedResult' => [['this'], ['sentence'], ['has'], ['no'], ['synonyms']]
            ],
            'oneMoreTest' => [
                'phrase' => 'schlicht',
                'expectedResult' => [['schlicht', 'natürlich']]
            ],
        ];
    }

    /**
     * @param string $phrase
     * @param array $expectedResult
     * @dataProvider loadGetSynonymsForPhraseDataProvider
     */
    public function testGetSynonymsForPhrase($phrase, $expectedResult)
    {
        $synonyms = $this->synAnalyzer->getSynonymsForPhrase($phrase);
        $this->assertEquals($expectedResult, $synonyms);
    }
}
