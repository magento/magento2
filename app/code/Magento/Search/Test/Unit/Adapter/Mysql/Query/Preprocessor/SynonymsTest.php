<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Adapter\Mysql\Query\Preprocessor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SynonymsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Api\SynonymAnalyzerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $synAnalyzer;

    /**
     * @var \Magento\Search\Adapter\Mysql\Query\Preprocessor\Synonyms
     */
    private $synPreprocessor;

    protected function setUp()
    {
        $objectMgr = new ObjectManager($this);

        $this->synAnalyzer = $this->getMockBuilder('Magento\Search\Model\SynonymAnalyzer')
            ->setMethods(['getSynonymsForPhrase'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->synPreprocessor = $objectMgr->getObject(
            'Magento\Search\Adapter\Mysql\Query\Preprocessor\Synonyms',
            [
                'synonymsAnalyzer' => $this->synAnalyzer
            ]
        );
    }

    public function testProcess()
    {
        $this->synAnalyzer->expects($this->once())
            ->method('getSynonymsForPhrase')
            ->with($this->equalTo('big'))
            ->will($this->returnValue([['big', 'huge']]));

        $result = $this->synPreprocessor->process('big');
        $this->assertEquals('big huge', $result);
    }
}
