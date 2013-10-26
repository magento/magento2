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
 * @category    Magento
 * @package     \Magento\Backup
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backup;

class NomediaTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $dir = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $backupFactory = $this->getMock('Magento\Backup\Factory', array(), array(), '', false);
        $snapshot = $this->getMock('Magento\Backup\Snapshot', array('create'), array($dir, $backupFactory));
        $snapshot->expects($this->any())
            ->method('create')
            ->will($this->returnValue(true));


        $model = new \Magento\Backup\Nomedia($snapshot);

        $rootDir = __DIR__ . DIRECTORY_SEPARATOR . '_files';

        $model = new \Magento\Backup\Nomedia($snapshot);
        $model->setRootDir($rootDir);

        $this->assertTrue($model->create());
        $this->assertEquals(
            array(
                $rootDir . DIRECTORY_SEPARATOR . 'media',
                $rootDir . DIRECTORY_SEPARATOR . 'pub' . DIRECTORY_SEPARATOR . 'media',
            ),
            $snapshot->getIgnorePaths()
        );
    }
}
