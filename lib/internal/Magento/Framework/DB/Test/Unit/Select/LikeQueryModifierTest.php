<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\LikeQueryModifier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class LikeQueryModifierTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return void
     */
    public function testModify(): void
    {
        $values = [
            'field1' => 'pattern1',
            'field2' => 'pattern2'
        ];
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock
            ->method('where')
            ->withConsecutive(
                ['field1 LIKE (?)', 'pattern1'],
                ['field2 LIKE (?)', 'pattern2']
            );
        $likeQueryModifier = $this->objectManager->getObject(
            LikeQueryModifier::class,
            ['values' => $values]
        );
        $likeQueryModifier->modify($selectMock);
    }
}
