<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Tools\Dependency\Report\Builder;

class AbstractBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\ParserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dependenciesParserMock;

    /**
     * @var \Magento\Tools\Dependency\Report\WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportWriterMock;

    /**
     * @var \Magento\Tools\Dependency\Report\Builder\AbstractBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builder;

    protected function setUp()
    {
        $this->dependenciesParserMock = $this->getMock('Magento\Tools\Dependency\ParserInterface');
        $this->reportWriterMock = $this->getMock('Magento\Tools\Dependency\Report\WriterInterface');

        $this->builder = $this->getMockForAbstractClass('Magento\Tools\Dependency\Report\Builder\AbstractBuilder', [
            'dependenciesParser' => $this->dependenciesParserMock,
            'reportWriter' => $this->reportWriterMock,
        ]);
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Passed option section "parse" is wrong.
     * @dataProvider dataProviderWrongParseOptions
     */
    public function testBuildWithWrongParseOptions($options)
    {
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongParseOptions()
    {
        return [
            [['write' => [1, 2]]],
            [['parse' => [], 'write' => [1, 2]]],
        ];
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Passed option section "write" is wrong.
     * @dataProvider dataProviderWrongWriteOptions
     */
    public function testBuildWithWrongWriteOptions($options)
    {
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongWriteOptions()
    {
        return [
            [['parse' => [1, 2]]],
            [['parse' => [1, 2], 'write' => []]],
        ];
    }

    public function testBuild()
    {
        $options = [
            'parse' => [
                'files_for_parse' => [1, 2, 3],
            ],
            'write' => [
                'report_filename' => 'some_filename'
            ],
        ];


        $parseResult = ['foo', 'bar', 'baz'];
        $configMock = $this->getMock('\Magento\Tools\Dependency\Report\Data\ConfigInterface');

        $this->dependenciesParserMock->expects($this->once())->method('parse')->with($options['parse'])
            ->will($this->returnValue($parseResult));
        $this->builder->expects($this->once())->method('buildData')->with($parseResult)
            ->will($this->returnValue($configMock));
        $this->reportWriterMock->expects($this->once())->method('write')
            ->with($options['write'], $configMock);

        $this->builder->build($options);
    }
}
