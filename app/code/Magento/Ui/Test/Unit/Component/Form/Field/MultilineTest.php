<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Field;

use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Multiline;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class MultilineTest
 *
 * Test for class \Magento\Ui\Component\Form\Element\Multiline
 */
class MultilineTest extends \PHPUnit_Framework_TestCase
{
    const NAME = 'test-name';

    /**
     * @var Multiline
     */
    protected $multiline;

    /**
     * @var UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->uiComponentFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);

        $this->multiline = new Multiline(
            $this->contextMock,
            $this->uiComponentFactoryMock
        );
    }

    /**
     * Run test for prepare method
     *
     * @param array $data
     * @return void
     *
     * @dataProvider prepareDataProvider
     */
    public function testPrepare(array $data)
    {
        $this->uiComponentFactoryMock->expects($this->exactly($data['config']['size']))
            ->method('create')
            ->with($this->stringContains(self::NAME . '_'), Field::NAME, $this->logicalNot($this->isEmpty()))
            ->willReturn($this->getComponentMock($data['config']['size']));

        $this->multiline->setData($data);
        $this->multiline->prepare();

        $result = $this->multiline->getData();

        $this->assertEquals($data, $result);
    }

    /**
     * @param int $exactly
     * @return UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getComponentMock($exactly)
    {
        $componentMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
            ->getMockForAbstractClass();

        $componentMock->expects($this->exactly($exactly))
            ->method('prepare');

        return $componentMock;
    }

    /**
     * Data provider for testPrepare
     *
     * @return array
     */
    public function prepareDataProvider()
    {
        return [
            [
                'data' => [
                    'name' => self::NAME,
                    'config' => [
                        'size' => 2,
                    ]
                ],
            ],
            [
                'data' => [
                    'name' => self::NAME,
                    'config' => [
                        'size' => 3,
                    ]
                ],
            ],
            [
                'data' => [
                    'name' => self::NAME,
                    'config' => [
                        'size' => 1,
                    ]
                ],
            ],
            [
                'data' => [
                    'name' => self::NAME,
                    'config' => [
                        'size' => 5,
                    ]
                ],
            ],
        ];
    }
}
