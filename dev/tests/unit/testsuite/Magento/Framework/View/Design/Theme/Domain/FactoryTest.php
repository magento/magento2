<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme domain model
 */
namespace Magento\Framework\View\Design\Theme\Domain;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreate()
    {
        $themeMock = $this->getMock('Magento\Core\Model\Theme', ['__wakeup', 'getType'], [], '', false);
        $themeMock->expects(
            $this->any()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL)
        );

        $newThemeMock = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\View\Design\Theme\Domain\VirtualInterface',
            ['theme' => $themeMock]
        )->will(
            $this->returnValue($newThemeMock)
        );

        $themeDomainFactory = new \Magento\Framework\View\Design\Theme\Domain\Factory($objectManager);
        $this->assertEquals($newThemeMock, $themeDomainFactory->create($themeMock));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreateWithWrongThemeType()
    {
        $wrongThemeType = 'wrong_theme_type';
        $themeMock = $this->getMock('Magento\Core\Model\Theme', ['__wakeup', 'getType'], [], '', false);
        $themeMock->expects($this->any())->method('getType')->will($this->returnValue($wrongThemeType));

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $themeDomainFactory = new \Magento\Framework\View\Design\Theme\Domain\Factory($objectManager);

        $this->setExpectedException(
            'Magento\Framework\Exception',
            sprintf('Invalid type of theme domain model "%s"', $wrongThemeType)
        );
        $themeDomainFactory->create($themeMock);
    }
}
