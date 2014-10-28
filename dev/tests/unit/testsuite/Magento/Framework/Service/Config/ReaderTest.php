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
namespace Magento\Framework\Service\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Service\Config\Reader
     */
    protected $_reader;

    /**
     * Prepare parameters
     */
    public function setUp()
    {
        $fileResolver = $this->getMockBuilder('Magento\Framework\App\Config\FileResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $converter = $this->getMockBuilder('Magento\Framework\Service\Config\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $schema = $this->getMockBuilder('Magento\Framework\Service\Config\SchemaLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder('\Magento\Framework\Config\ValidationStateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_reader = new \Magento\Framework\Service\Config\Reader($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf('Magento\Framework\Service\Config\Reader', $this->_reader);
    }
}
