<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_modulesReader;

    /**
     * @var  \Magento\Persistent\Helper\Data
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_modulesReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            \Magento\Persistent\Helper\Data::class,
            ['modulesReader' => $this->_modulesReader]
        );
    }

    public function testGetPersistentConfigFilePath()
    {
        $this->_modulesReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Persistent'
        )->willReturn(
            'path123'
        );
        $this->assertEquals('path123/persistent.xml', $this->_helper->getPersistentConfigFilePath());
    }
}
