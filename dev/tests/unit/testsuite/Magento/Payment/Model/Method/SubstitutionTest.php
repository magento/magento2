<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Payment\Model\Method;

class SubstitutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Payment\Model\Method\Substitution
     */
    protected $model;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject('Magento\Payment\Model\Method\Substitution');
    }

    public function testGetTitle()
    {
        $infoMock = $this->getMockBuilder(
            'Magento\Payment\Model\Info'
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
