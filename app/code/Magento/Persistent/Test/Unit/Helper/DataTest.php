<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Module\Dir\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Persistent\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    protected $_modulesReader;

    /**
     * @var  Data
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_modulesReader = $this->createMock(Reader::class);
        $objectManager = new ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            Data::class,
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
        )->will(
            $this->returnValue('path123')
        );
        $this->assertEquals('path123/persistent.xml', $this->_helper->getPersistentConfigFilePath());
    }
}
