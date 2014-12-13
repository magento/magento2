<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Framework;

use Magento\TestFramework\Helper\ObjectManager;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\Report\Framework\Builder
     */
    protected $builder;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->builder = $objectManagerHelper->getObject('Magento\Tools\Dependency\Report\Framework\Builder');
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parse error. Passed option "config_files" is wrong.
     * @dataProvider dataProviderWrongOptionConfigFiles
     */
    public function testBuildWithWrongOptionConfigFiles($options)
    {
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionConfigFiles()
    {
        return [
            [
                [
                    'parse' => ['files_for_parse' => [1, 2], 'config_files' => []],
                    'write' => [1, 2],
                ],
            ],
            [['parse' => ['files_for_parse' => [1, 2]], 'write' => [1, 2]]]
        ];
    }
}
