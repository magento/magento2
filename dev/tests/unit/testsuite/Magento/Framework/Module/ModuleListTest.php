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
namespace Magento\Framework\Module;

class ModuleListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;


    protected function setUp()
    {
        $this->cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
        $this->readerMock = $this->getMock(
            'Magento\Framework\Module\Declaration\Reader\Filesystem',
            array(),
            array(),
            '',
            false
        );
    }

    public function testGetModulesWhenDataIsCached()
    {
        $data = array(
            'declared_module' => array(
                'name' => 'declared_module',
                'version' => '1.0.0.0',
                'active' => false,
            ),
        );
        $cacheId = 'global::modules_declaration_cache';
        $this->cacheMock->expects($this->once())->method('load')->with($cacheId)->will($this->returnValue(
            serialize($data)
        ));
        $this->readerMock->expects($this->never())->method('read');
        $this->cacheMock->expects($this->never())->method('save');
        $model = new ModuleList(
            $this->readerMock,
            $this->cacheMock
        );
        $this->assertEquals($data, $model->getModules());
    }

    public function testGetModuleWhenDataIsNotCached()
    {
        $moduleData = array(
            'name' => 'declared_module',
            'version' => '1.0.0.0',
            'active' => false,
        );
        $data = array(
            'declared_module' => $moduleData,
        );
        $cacheId = 'global::modules_declaration_cache';
        $this->cacheMock->expects($this->once())->method('load')->with($cacheId);
        $this->readerMock->expects($this->once())->method('read')->with('global')->will($this->returnValue($data));
        $this->cacheMock->expects($this->once())->method('save')->with(serialize($data), $cacheId);
        $model = new ModuleList(
            $this->readerMock,
            $this->cacheMock
        );
        $this->assertEquals($moduleData, $model->getModule('declared_module'));
        $this->assertNull($model->getModule('not_declared_module'));
    }
}
