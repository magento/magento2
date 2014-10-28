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
namespace Magento\DesignEditor\Model\Plugin;

class ThemeCopyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Model\Plugin\ThemeCopyService
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->factoryMock = $this->getMock(
            'Magento\DesignEditor\Model\Theme\ChangeFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->subjectMock = $this->getMock('Magento\Theme\Model\CopyService', array(), array(), '', false);
        $this->model = new \Magento\DesignEditor\Model\Plugin\ThemeCopyService($this->factoryMock);
    }

    public function testAroundCopySavesChangeTimeIfSourceThemeHasBeenAlreadyChanged()
    {
        $sourceThemeId = 1;
        $sourceChangeTime = '21:00:00';
        $targetThemeId = 2;

        $sourceThemeMock = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $sourceThemeMock->expects($this->any())->method('getId')->will($this->returnValue($sourceThemeId));

        $targetThemeMock = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $targetThemeMock->expects($this->any())->method('getId')->will($this->returnValue($targetThemeId));

        $sourceChangeMock = $this->getMock(
            'Magento\DesignEditor\Model\Theme\Change',
            array('getId', 'getChangeTime', 'loadByThemeId', '__wakeup'),
            array(),
            '',
            false
        );
        $targetChangeMock = $this->getMock(
            'Magento\DesignEditor\Model\Theme\Change',
            array('setThemeId', 'setChangeTime', 'loadByThemeId', 'save', '__wakeup'),
            array(),
            '',
            false
        );
        $this->factoryMock->expects($this->at(0))->method('create')->will($this->returnValue($sourceChangeMock));
        $this->factoryMock->expects($this->at(1))->method('create')->will($this->returnValue($targetChangeMock));

        $sourceChangeMock->expects($this->once())->method('loadByThemeId')->with($sourceThemeId);
        $sourceChangeMock->expects($this->any())->method('getId')->will($this->returnValue(10));
        $sourceChangeMock->expects($this->any())->method('getChangeTime')->will($this->returnValue($sourceChangeTime));

        $targetChangeMock->expects($this->once())->method('loadByThemeId')->with($targetThemeId);
        $targetChangeMock->expects($this->once())->method('setThemeId')->with($targetThemeId);
        $targetChangeMock->expects($this->once())->method('setChangeTime')->with($sourceChangeTime);
        $targetChangeMock->expects($this->once())->method('save');

        $closureMock = function () {
        };
        $this->model->aroundCopy($this->subjectMock, $closureMock, $sourceThemeMock, $targetThemeMock);
    }
}
