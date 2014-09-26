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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('create'),
            array(),
            '',
            false
        );
        $this->responseMock = $this->getMock('Magento\Framework\App\Console\Response', array(), array(), '', false);
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
        $shell = $this->getMock('Magento\Indexer\Model\Shell', array(), array(), '', false);
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
        return array(array(true), array(false));
    }

    public function testCatchException()
    {
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', array(), array(), '', false);
        $this->assertFalse($this->entryPoint->catchException($bootstrap, new \Exception));
    }
}
