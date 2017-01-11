<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl;

use Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl\AbstractCollectionStub;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for version control abstract collection model.
 */
class AbstractCollectionTest extends \Magento\Eav\Test\Unit\Model\Entity\Collection\AbstractCollectionTest
{
    /**
     * Subject of testing.
     *
     * @var AbstractCollectionStub|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshot;

    protected function setUp()
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);

        $this->entitySnapshot = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class,
            ['registerSnapshot'],
            [],
            '',
            false
        );

        $this->subject = $objectManager->getObject(
            \Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl\AbstractCollectionStub::class,
            [
                'entityFactory' => $this->coreEntityFactoryMock,
                'universalFactory' => $this->validatorFactoryMock,
                'entitySnapshot' => $this->entitySnapshot
            ]
        );
    }

    /**
     * @param array $data
     * @dataProvider fetchItemDataProvider
     */
    public function testFetchItem(array $data)
    {
        $item = $this->getMagentoObject()->setData($data);

        $this->statementMock->expects($this->once())
            ->method('fetch')
            ->willReturn($data);

        if (!$data) {
            $this->entitySnapshot->expects($this->never())->method('registerSnapshot');

            $this->assertEquals(false, $this->subject->fetchItem());
        } else {
            $this->entitySnapshot->expects($this->once())->method('registerSnapshot')->with($item);

            $this->assertEquals($item, $this->subject->fetchItem());
        }
    }

    public static function fetchItemDataProvider()
    {
        return [
            [[]],
            [['attribute' => 'test']]
        ];
    }
}
