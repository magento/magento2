<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\View\Layout\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Model\View\Layout\Filter\Acl;
use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\AuthorizationInterface;

class AclTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Acl
     */
    protected $model;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var StructureManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    protected function setUp()
    {
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMock();
        $this->structureManager = $this->getMockBuilder(StructureManager::class)
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Acl::class,
            [
                'authorization' => $this->authorizationMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->model, 'structureManager', $this->structureManager);
    }

    public function testFilterAclElements()
    {
        $scheduledStructureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $structureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Data\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elements = [
            'element_0' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_0',
                    ],
                ],
            ],
            'element_1' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_1',
                        'acl' => 'acl_authorised',
                    ],
                ],
            ],
            'element_2' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_2',
                        'acl' => 'acl_non_authorised',
                    ],
                ],
            ],
            'element_3' => [
                0 => '',
                1 => [
                    'attributes' => [
                        'name' => 'element_3',
                        'acl' => 'acl_non_authorised',
                    ],
                ],
            ],
        ];

        $scheduledStructureMock->expects($this->once())
            ->method('getElements')
            ->willReturn($elements);

        $this->authorizationMock->expects($this->exactly(3))
            ->method('isAllowed')
            ->willReturnMap(
                [
                    ['acl_authorised', null, true],
                    ['acl_non_authorised', null, false],
                ]
            );

        $this->structureManager->expects($this->exactly(2))
            ->method('removeElement')
            ->willReturnMap(
                [
                    ['element_2', true, true],
                    ['element_3', true, true],
                ]
            );

        $this->model->filterAclElements($scheduledStructureMock, $structureMock);
    }
}
