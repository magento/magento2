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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Autoload;

use \Composer\Autoload\ClassLoader;
use \Magento\TestFramework\Helper\ObjectManager;

class ClassLoaderWrapperTest extends \PHPUnit_Framework_TestCase
{

    const PREFIX = 'Namespace\\Prefix\\';

    const DIR = '/path/to/class/';

    const DEFAULT_PREPEND = false;


    /**
     * @var ClassLoader | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $autoloaderMock;

    /**
     * @var \Magento\Framework\Autoload\ClassLoaderWrapper
     */
    protected $model;

    public function setUp()
    {
        $this->autoloaderMock = $this->getMock('Composer\Autoload\ClassLoader');
        $this->model = (new ObjectManager($this))->getObject('Magento\Framework\Autoload\ClassLoaderWrapper',
            [
                'autoloader' => $this->autoloaderMock
            ]
        );
    }

    public function testAdd()
    {
        $prepend = true;

        $this->autoloaderMock->expects($this->once())
            ->method('add')
            ->with(self::PREFIX, self::DIR, $prepend);

        $this->model->addPsr0(self::PREFIX, self::DIR, $prepend);
    }

    public function testAddPsr4()
    {
        $prepend = true;

        $this->autoloaderMock->expects($this->once())
            ->method('addPsr4')
            ->with(self::PREFIX, self::DIR, $prepend);

        $this->model->addPsr4(self::PREFIX, self::DIR, $prepend);
    }

    public function testAddDefault()
    {
        $this->autoloaderMock->expects($this->once())
            ->method('add')
            ->with(self::PREFIX, self::DIR, self::DEFAULT_PREPEND);

        $this->model->addPsr0(self::PREFIX, self::DIR);
    }

    public function testAddPsr4Default()
    {
        $this->autoloaderMock->expects($this->once())
            ->method('addPsr4')
            ->with(self::PREFIX, self::DIR, self::DEFAULT_PREPEND);

        $this->model->addPsr4(self::PREFIX, self::DIR);
    }

    public function testSet()
    {
        $paths = [self::DIR];
        $this->autoloaderMock->expects($this->once())
            ->method('set')
            ->with(self::PREFIX, $paths);

        $this->model->setPsr0(self::PREFIX, $paths);
    }

    public function testSetPsr4()
    {
        $paths = [self::DIR];
        $this->autoloaderMock->expects($this->once())
            ->method('setPsr4')
            ->with(self::PREFIX, $paths);

        $this->model->setPsr4(self::PREFIX, $paths);
    }
}
