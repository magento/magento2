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

namespace Magento\Framework\Object\Copy\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\Object\Copy\Config\SchemaLocator
     */
    protected $model;

    protected function setUp()
    {
        $this->fileSystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array(),
            array(),
            '',
            false
        );
        $this->fileSystemMock->expects($this->any())
            ->method('getPath')
            ->with(\Magento\Framework\App\Filesystem::ROOT_DIR)
            ->will($this->returnValue('schema_dir'));

        $this->model = new \Magento\Framework\Object\Copy\Config\SchemaLocator(
            $this->fileSystemMock,
            'schema.xsd',
            'perFileSchema.xsd'
        );
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/schema.xsd', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/perFileSchema.xsd', $this->model->getPerFileSchema());
    }
}
