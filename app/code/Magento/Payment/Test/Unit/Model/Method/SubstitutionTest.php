<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\Method\Substitution;
use PHPUnit\Framework\TestCase;

class SubstitutionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Substitution
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(Substitution::class);
    }

    public function testGetTitle()
    {
        $infoMock = $this->getMockBuilder(
            Info::class
        )->disableOriginalConstructor()
            ->setMethods(
                []
            )->getMock();

        $this->model->setInfoInstance($infoMock);
        $expectedResult = 'StringTitle';
        $infoMock->expects(
            $this->once()
        )->method(
            'getAdditionalInformation'
        )->with(
            Substitution::INFO_KEY_TITLE
        )->willReturn(
            $expectedResult
        );

        $this->assertEquals($expectedResult, $this->model->getTitle());
    }
}
