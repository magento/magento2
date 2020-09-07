<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @magentoDataFixture Magento/Search/_files/synonym_reader.php
 * @magentoDbIsolation disabled
 */
class SynonymAnalyzerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymAnalyzer
     */
    private $synAnalyzer;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->synAnalyzer = $objectManager->get(\Magento\Search\Model\SynonymAnalyzer::class);
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
            'WithCaseMismatch' => [
                'phrase' => 'GNU\'s Not Unix',
                'expectedResult' => [['GNU\'s'], ['Not'], ['unix', 'linux'],]
            ],
            'WithMultiWordPhrase' => [
                'phrase' => 'Coastline of Great Britain stretches for 11,073 miles',
                'expectedResult' => [
                    ['Coastline'],
                    ['of'],
                    ['Great Britain', 'United Kingdom'],
                    ['Britain'],
                    ['stretches'],
                    ['for'],
                    ['11,073'],
                    ['miles']
                ]
            ],
            'PartialSynonymMatching' => [
                'phrase' => 'Magento Engineering',
                'expectedResult' => [
                    ['orange', 'magento'],
                    ['Engineering', 'Technical Staff']
                ]
            ],
            'noSynonyms' => [
                'phrase' => 'this sentence has no synonyms',
                'expectedResult' => [['this'], ['sentence'], ['has'], ['no'], ['synonyms']]
            ],
            'multipleSpaces' => [
                'phrase' => 'GNU\'s Not   Unix',
                'expectedResult' => [['GNU\'s'], ['Not'], ['unix', 'linux'],]
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
