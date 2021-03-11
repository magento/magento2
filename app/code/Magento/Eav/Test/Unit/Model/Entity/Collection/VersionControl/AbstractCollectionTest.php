<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Collection\VersionControl;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for version control abstract collection model.
 */
class AbstractCollectionTest extends \Magento\Eav\Test\Unit\Model\Entity\Collection\AbstractCollectionTest
{
    /**
     * Subject of testing.
     *
     * @var AbstractCollectionStub|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subject;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshot;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);

        $this->entitySnapshot = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class,
            ['registerSnapshot']
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
