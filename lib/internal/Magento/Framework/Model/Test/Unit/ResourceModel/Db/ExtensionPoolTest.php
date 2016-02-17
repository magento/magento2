<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

/**
 * Unit test for ExtensionPool class.
 */
class ExtensionPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\ExtensionPool
     */
    protected $subject;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnArgument(0);

        $this->subject = new \Magento\Framework\Model\ResourceModel\Db\ExtensionPool(
            $this->objectManager,
            [
                'test_extension_1' => [
                    'default' => [
                        'read' => 'Test\Extension1\Default\CreateHandler',
                    ],
                    'Test\First\Entity' => [
                        'read' => 'Test\Extension1\Entity\ReadHandler',
                    ]
                ],
                'test_extension_2' => [
                    'default' => [
                        'read' => 'Test\Extension2\Default\CreateHandler',
                    ],
                    'Test\Second\Entity' => [
                        'read' => 'Test\Extension2\Entity\ReadHandler',
                    ]
                ]
            ]
        );
    }

    /**
     * @dataProvider executeDataProvider
     *
     * @param array $expected
     * @param string $entityType
     * @param string $actionName
     * @return void
     */
    public function testExecute(array $expected, $entityType, $actionName)
    {
        $this->assertEquals(
            $expected,
            $this->subject->getActions($entityType, $actionName)
        );
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                [
                    'test_extension_1' => 'Test\Extension1\Entity\ReadHandler',
                    'test_extension_2' => 'Test\Extension2\Default\CreateHandler'
                ],
                'Test\First\Entity',
                'read'
            ],
            [
                [],
                'Test\First\Entity',
                'delete'
            ]
        ];
    }
}
