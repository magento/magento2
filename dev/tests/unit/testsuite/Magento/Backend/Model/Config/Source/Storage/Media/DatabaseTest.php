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
namespace Magento\Backend\Model\Config\Source\Storage\Media;

/**
 * Class DatabaseTest
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Source\Storage\Media\Database
     */
    protected $mediaDatabase;

    /**
     * @var \Magento\Framework\App\Arguments|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\Arguments', array(), array(), '', false);
        $this->configMock->expects(
            $this->any()
        )->method(
            'getResources'
        )->will(
            $this->returnValue(
                array('default_setup' => array('default_setup'), 'custom_resource' => array('custom_resource'))
            )
        );
        $this->mediaDatabase = new \Magento\Backend\Model\Config\Source\Storage\Media\Database($this->configMock);
    }

    /**
     * test to option array
     */
    public function testToOptionArray()
    {
        $this->assertNotEquals(
            $this->mediaDatabase->toOptionArray(),
            array(
                array('value' => 'default_setup', 'label' => 'default_setup'),
                array('value' => 'custom_resource', 'label' => 'custom_resource')
            )
        );
        $this->assertEquals(
            $this->mediaDatabase->toOptionArray(),
            array(
                array('value' => 'custom_resource', 'label' => 'custom_resource'),
                array('value' => 'default_setup', 'label' => 'default_setup')
            )
        );
        $this->assertEquals(
            current($this->mediaDatabase->toOptionArray()),
            array('value' => 'custom_resource', 'label' => 'custom_resource')
        );
    }
}
