<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for version control abstract collection model.
 */
class AbstractCollectionTest extends \Magento\Eav\Test\Unit\Model\Entity\Collection\AbstractCollectionTest
{
    /**
     * Subject of testing.
     *
     * @var AbstractCollectionStub|MockObject
     */
    protected $subject;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshot;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);

        $this->entitySnapshot = $this->createPartialMock(
            Snapshot::class,
            ['registerSnapshot']
        );

        $this->subject = $objectManager->getObject(
            AbstractCollectionStub::class,
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

            $this->assertFalse($this->subject->fetchItem());
        } else {
            $this->entitySnapshot->expects($this->once())->method('registerSnapshot')->with($item);

            $this->assertEquals($item, $this->subject->fetchItem());
        }
    }

    /**
     * @return array
     */
    public static function fetchItemDataProvider()
    {
        return [
            [[]],
            [['attribute' => 'test']]
        ];
    }
}
