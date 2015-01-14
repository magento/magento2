<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Config\Control;

class QuickStylesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSchemaFile()
    {
        /** @var $moduleReader \Magento\Framework\Module\Dir\Reader|PHPUnit_Framework_MockObject_MockObject */
        $moduleReader = $this->getMockBuilder(
            'Magento\Framework\Module\Dir\Reader'
        )->setMethods(
            ['getModuleDir']
        )->disableOriginalConstructor()->getMock();

        $moduleReader->expects(
            $this->any(),
            $this->any()
        )->method(
            'getModuleDir'
        )->will(
            $this->returnValue('/base_path/etc')
        );

        /** @var $quickStyle \Magento\DesignEditor\Model\Config\Control\QuickStyles */
        $quickStyle = $this->getMock(
            'Magento\DesignEditor\Model\Config\Control\QuickStyles',
            null,
            ['moduleReader' => $moduleReader, 'configFiles' => ['sample']],
            '',
            false
        );

        $property = new \ReflectionProperty($quickStyle, '_moduleReader');
        $property->setAccessible(true);
        $property->setValue($quickStyle, $moduleReader);

        $this->assertStringMatchesFormat('%s/etc/quick_styles.xsd', $quickStyle->getSchemaFile());
    }
}
