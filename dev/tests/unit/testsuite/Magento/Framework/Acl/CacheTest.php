<?php
/**
 *
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
namespace Magento\Framework\Acl;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache
     */
    protected $model;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheConfig;

    /**
     * @var string
     */
    protected $cacheKey;

    protected function setUp()
    {
        $this->cacheConfig = $this->getMock('Magento\Framework\Config\CacheInterface');
        $this->cacheKey = 'test_key';

        $this->model = new Cache($this->cacheConfig, $this->cacheKey);
    }

    /**
     * @param array|bool $dataAcl
     * @dataProvider aclDataProvider
     */
    public function testGet($dataAcl)
    {
        $this->initAcl((is_array($dataAcl) ? serialize($dataAcl): $dataAcl));
        $this->assertEquals($dataAcl, $this->model->get());
    }

    /**
     * @return array
     */
    public function aclDataProvider()
    {
        return [
            ['dataAcl' => ['someKey' => 'someValue']],
            ['dataAcl' => false]
        ];
    }

    /**
     * @param bool $expectedTest
     * @dataProvider hasWithoutAclDataProvider
     */
    public function testHasWithoutAcl($expectedTest)
    {
        $this->cacheConfig->expects($this->once())->method('test')->will($this->returnValue($expectedTest));
        $this->assertEquals($expectedTest, $this->model->has());
    }

    /**
     * @return array
     */
    public function hasWithoutAclDataProvider()
    {
        return [
            ['expectedTest' => true],
            ['expectedTest' => false]
        ];
    }

    /**
     * @param array|bool $dataAcl
     * @dataProvider aclDataProvider
     */
    public function testHasWithAcl($dataAcl)
    {
        $this->initAcl((is_array($dataAcl) ? serialize($dataAcl): $dataAcl));
        $this->cacheConfig->expects($this->never())->method('test');

        $this->model->get();
        $this->assertTrue($this->model->has());
    }

    protected function initAcl($aclData)
    {
        $this->cacheConfig->expects($this->once())
            ->method('load')
            ->with($this->cacheKey)
            ->will($this->returnValue($aclData));
    }

    public function testSave()
    {
        $acl = $this->getMockBuilder('Magento\Framework\Acl')->disableOriginalConstructor()->getMock();

        $this->cacheConfig->expects($this->once())->method('save')->with(serialize($acl), $this->cacheKey);
        $this->model->save($acl);
    }

    public function testClean()
    {
        $this->cacheConfig->expects($this->once())
            ->method('remove')
            ->with($this->cacheKey);
        $this->model->clean();
    }
}
