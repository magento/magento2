<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Adapter\Query\Preprocessor;

use Magento\Search\Api\SynonymAnalyzerInterface;
use Magento\Search\Adapter\Query\Preprocessor\Synonyms as SynonymsPreprocessor;
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
            '\Magento\Search\Adapter\Query\Preprocessor\Synonyms',
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
