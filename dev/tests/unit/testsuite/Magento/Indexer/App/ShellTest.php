<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\App;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\App\Shell
     */
    protected $entryPoint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shellFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    protected function setUp()
    {
        $this->shellFactoryMock = $this->getMock(
            'Magento\Indexer\Model\ShellFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
        $this->entryPoint = new \Magento\Indexer\App\Shell(
            'indexer.php',
            $this->shellFactoryMock,
            $this->responseMock
        );
    }

    /**
     * @param boolean $shellHasErrors
     * @dataProvider processRequestDataProvider
     */
    public function testProcessRequest($shellHasErrors)
    {
        $shell = $this->getMock('Magento\Indexer\Model\Shell', [], [], '', false);
        $shell->expects($this->once())->method('hasErrors')->will($this->returnValue($shellHasErrors));
        $shell->expects($this->once())->method('run');
        $this->shellFactoryMock->expects($this->any())->method('create')->will($this->returnValue($shell));

        $this->entryPoint->launch();
    }

    /**
     * @return array
     */
    public function processRequestDataProvider()
    {
        return [[true], [false]];
    }

    public function testCatchException()
    {
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        $this->assertFalse($this->entryPoint->catchException($bootstrap, new \Exception()));
    }
}
