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
namespace Magento\App\Response;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Response\Http
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\App\Response\Http();
        $this->_model->headersSentThrowsException = false;
        $this->_model->setHeader('name', 'value');
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetHeaderWhenHeaderNameIsEqualsName()
    {
        $expected = array('name' => 'Name', 'value' => 'value', 'replace' => false);
        $actual = $this->_model->getHeader('Name');
        $this->assertEquals($expected, $actual);
    }

    public function testGetHeaderWhenHeaderNameIsNotEqualsName()
    {
        $this->assertFalse($this->_model->getHeader('Test'));
    }

    public function testGetVaryString()
    {
        $vary = array('some-vary-key' => 'some-vary-value');
        ksort($vary);
        $expected = sha1(serialize($vary));
        $this->_model->setVary('some-vary-key', 'some-vary-value');

        $this->assertEquals($expected, $this->_model->getVaryString());
    }
}
