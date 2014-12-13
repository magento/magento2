<?php
/**
 * Unit test for \Magento\Framework\Filesystem\FileResolver
 *
 * Only one method is unit testable, other methods require integration testing.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filesystem;

use Magento\TestFramework\Helper\ObjectManager;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\FileResolver
     */
    protected $model;

    public function setUp()
    {
        $this->model = (new ObjectManager($this))->getObject('Magento\Framework\Filesystem\FileResolver');
    }

    public function testGetFilePath()
    {
        $this->assertSame('Path/To/My/Class.php', $this->model->getFilePath('Path\To\My_Class'));
    }
}
