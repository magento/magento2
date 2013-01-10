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
 * @category   Magento
 * @package    tools
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../') . '/tools/migration/System/Writer/Factory.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../') . '/tools/migration/System/Writer/FileSystem.php';

class Tools_Migration_System_Writer_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tools_Migration_System_Writer_Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Tools_Migration_System_Writer_Factory();
    }

    public function testGetWriterReturnsProperWriter()
    {
        $this->assertInstanceOf('Tools_Migration_System_Writer_FileSystem', $this->_model->getWriter('write'));
        $this->assertInstanceOf('Tools_Migration_System_Writer_Memory', $this->_model->getWriter('someWriter'));
    }
}
