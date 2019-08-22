<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select\LikeQueryModifier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LikeQueryModifierTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testModify()
    {
        $values = [
            'field1' => 'pattern1',
            'field2' => 'pattern2',
        ];
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->at(0))
            ->method('where')
            ->with('field1 LIKE (?)', 'pattern1');
        $selectMock->expects($this->at(1))
            ->method('where')
            ->with('field2 LIKE (?)', 'pattern2');
        $likeQueryModifier = $this->objectManager->getObject(
            LikeQueryModifier::class,
            ['values' => $values]
        );
        $likeQueryModifier->modify($selectMock);
    }
}
