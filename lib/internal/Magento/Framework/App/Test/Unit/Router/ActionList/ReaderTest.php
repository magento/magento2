<?php
/**
 * RouterList model test class
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router\ActionList;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var \Magento\Framework\App\Router\ActionList\Reader
     */
    protected $actionListReader;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->moduleReaderMock = $this->getMockBuilder('Magento\Framework\Module\Dir\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionListReader = $this->objectManager->getObject(
            'Magento\Framework\App\Router\ActionList\Reader',
            ['moduleReader' => $this->moduleReaderMock]
        );
    }

    /**
     * @param array $actionFiles
     * @param array $expected
     * @dataProvider readDataProvider
     */
    public function testRead($actionFiles, $expected)
    {
        $this->moduleReaderMock->expects($this->once())
            ->method('getActionFiles')
            ->willReturn($actionFiles);
        $this->assertEquals($expected, $this->actionListReader->read());
    }

    public function readDataProvider()
    {
        return [
            [[], []],
            [
                [
                    'Magento/Backend/Controller/Adminhtml/Cache.php',
                    'Magento/Backend/Controller/Adminhtml/Index.php'
                ],
                [
                    'magento\backend\controller\adminhtml\cache' => 'Magento\Backend\Controller\Adminhtml\Cache',
                    'magento\backend\controller\adminhtml\index' => 'Magento\Backend\Controller\Adminhtml\Index'

                ]
            ]
        ];
    }
}
