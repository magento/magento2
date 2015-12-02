<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @magentoDataFixture Magento/Search/_files/SynonymReader.php
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


    public static function loadGetSynonymsForPhraseDataProvider()
    {
        return [
            [
                'Elizabeth is the English queen.',
                [['elizabeth'],['is'],['the'],['british', 'english'],['queen', 'monarch']]
            ],
            [
                'This sentence has no synonyms',
                [['this'], ['sentence'], ['has'], ['no'], ['synonyms']]
            ]
        ];
    }

    /**
     * @dataProvider loadGetSynonymsForPhraseDataProvider
     */
    public function testGetSynonymsForPhrase($phrase, $expectedResult)
    {
        $synonyms = $this->synAnalyzer->getSynonymsForPhrase($phrase);
        $this->assertEquals($expectedResult, $synonyms);
    }
}
