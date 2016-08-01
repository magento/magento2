<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Method;

class SubstitutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Payment\Model\Method\Substitution
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(\Magento\Payment\Model\Method\Substitution::class);
    }

    public function testGetTitle()
    {
        $infoMock = $this->getMockBuilder(
            \Magento\Payment\Model\Info::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->model->setInfoInstance($infoMock);
        $expectedResult = 'StringTitle';
        $infoMock->expects(
            $this->once()
        )->method(
            'getAdditionalInformation'
        )->with(
            \Magento\Payment\Model\Method\Substitution::INFO_KEY_TITLE
        )->will(
            $this->returnValue(
                $expectedResult
            )
        );

        $this->assertEquals($expectedResult, $this->model->getTitle());
    }
}
