<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Preprocessor;

use Magento\Search\Api\SynonymAnalyzerInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Synonyms as SynonymsPreprocessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SynonymsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SynonymsPreprocessor
     */
    protected $model;

    /**
     * @var SynonymAnalyzerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $synonymsAnalyzer;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->synonymsAnalyzer = $this->getMockBuilder('\Magento\Search\Api\SynonymAnalyzerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Elasticsearch\SearchAdapter\Query\Preprocessor\Synonyms',
            [
                'synonymsAnalyzer' => $this->synonymsAnalyzer,
            ]
        );
    }

    /**
     * Test process() method
     */
    public function testProcess()
    {
        $this->synonymsAnalyzer->expects($this->once())
            ->method('getSynonymsForPhrase')
            ->willReturn([
                ['red', 'blue']
            ]);

        $this->assertEquals(
            'red blue',
            $this->model->process('red')
        );
    }
}
