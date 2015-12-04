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

            // Test for a sentence with synonym words
            [
                'Elizabeth is the English queen.',
                [['elizabeth'],['is'],['the'],['british', 'english'],['queen', 'monarch']]
            ],

            // Test for no synonyms match
            [
                'This sentence has no synonyms',
                [['this'], ['sentence'], ['has'], ['no'], ['synonyms']]
            ],

            //Test for all the special characters being excluded
            [
                '~tilde`backtic! exclamation@  at#hash\$dollar%percent^carat&ampersand*star(leftparan)rightparan'
                . '_underscore+plus=equal{leftcurly}rightcurly[leftbracket]rightbracket:colon"doublequote'
                . '\'singlequote,comma  space.period<leftangle>rightangle?questionmark\\backslash/forwardslash'
                . '     tab;semicolon',
                [
                    ['tilde'],
                    ['backtic'],
                    ['exclamation'],
                    ['at'],
                    ['hash'],
                    ['dollar'],
                    ['percent'],
                    ['carat'],
                    ['ampersand'],
                    ['star'],
                    ['leftparan'],
                    ['rightparan'],
                    ['underscore'],
                    ['plus'],
                    ['equal'],
                    ['leftcurly'],
                    ['rightcurly'],
                    ['leftbracket'],
                    ['rightbracket'],
                    ['colon'],
                    ['doublequote'],
                    ['singlequote'],
                    ['comma'],
                    ['space'],
                    ['period'],
                    ['leftangle'],
                    ['rightangle'],
                    ['questionmark'],
                    ['backslash'],
                    ['forwardslash'],
                    ['tab'],
                    ['semicolon']
                ]
            ],

            //Test for non-ascii character set. Let's learn German!
            ['schlicht', [['schlicht', 'natürlich']]]
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
