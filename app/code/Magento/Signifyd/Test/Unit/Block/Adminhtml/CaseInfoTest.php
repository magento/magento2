<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Block\Adminhtml;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Block\Adminhtml\CaseInfo;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests for Signifyd block information.
 *
 * Class CaseInfoTest
 */
class CaseInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaseInterface|MockObject
     */
    private $caseEntity;

    /**
     * @var CaseInfo
     */
    private $caseInfo;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScore'])
            ->getMockForAbstractClass();

        $this->caseInfo = $objectManager->getObject(CaseInfo::class);
    }

    /**
     * Checks css class according to case entity score value.
     *
     * @param integer $score
     * @param string $expectedClassName
     * @covers \Magento\Signifyd\Block\CaseInfo::getScoreClass
     * @dataProvider getScoreClassDataProvider
     */
    public function testGetScoreClass($score, $expectedClassName)
    {
        $this->caseEntity->expects($this->once())
            ->method('getScore')
            ->willReturn($score);

        self::assertEquals(
            $expectedClassName,
            $this->caseInfo->getScoreClass($this->caseEntity)
        );
    }

    /**
     * Case scores and corresponding class name data provider

     * @return array
     */
    public function getScoreClassDataProvider()
    {
        return [
            [300, 'red'],
            [400, 'yellow'],
            [500, 'green'],
        ];
    }
}
