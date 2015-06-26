<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\FixtureModel;

class FixtureModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Setup\Fixtures\FixtureModel
     */
    private $model;

    public function setUp()
    {
        $reindexCommandMock = $this->getMock(
            '\Magento\Indexer\Console\Command\IndexerReindexCommand',
            [],
            [],
            '',
            false
        );
        $fileParserMock = $this->getMock('\Magento\Framework\XML\Parser', [], [], '', false);

        $this->model = new FixtureModel($reindexCommandMock, $fileParserMock);
    }

    public function testGetObjectManager()
    {
        $this->assertInstanceOf('Magento\Framework\ObjectManager\ObjectManager', $this->model->getObjectManager());
    }

    public function testReindex()
    {
        $outputMock = $this->getMock('\Symfony\Component\Console\Output\OutputInterface', [], [], '', false);
        $this->model->reindex($outputMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Profile configuration file `exception.file` is not readable or does not exists.
     */
    public function testLoadConfigException()
    {
        $this->model->loadConfig('exception.file');
    }

    public function testLoadConfig()
    {
        $reindexCommandMock = $this->getMock(
            '\Magento\Indexer\Console\Command\IndexerReindexCommand',
            [],
            [],
            '',
            false
        );

        $fileParserMock = $this->getMock('\Magento\Framework\XML\Parser', [], [], '', false);
        $fileParserMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->model = new FixtureModel($reindexCommandMock, $fileParserMock);
        $this->model->loadConfig('config.file');
    }

    public function testGetValue()
    {
        $this->assertSame(null, $this->model->getValue('null_key'));
    }
}

namespace Magento\Setup\Fixtures;

/**
 * Overriding the built-in PHP function since it cannot be mocked->
 *
 * The method is used in FixtureModel. loadConfig in an if statement. By overriding this method we are able to test
 * both of the possible cases based on the return value of is_readable.
 *
 * @param string $filename
 * @return bool
 */
function is_readable($filename)
{
    if (strpos($filename, 'exception') !== false) {
        return false;
    }
    return true;
}
