<?php
/**
 * Tests Magento\Core\Model\Asset\Plugin\CleanMergedJsCss
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
namespace Magento\Core\Model\Asset\Plugin;

class CleanMergedJsCssTest extends \Magento\Test\BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\File\Storage\Database
     */
    private $databaseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Filesystem
     */
    private $filesystemMock;

    /**
     * @var bool
     */
    private $hasBeenCalled = false;

    /**
     * @var \Magento\Core\Model\Asset\Plugin\CleanMergedJsCss
     */
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->filesystemMock = $this->basicMock('\Magento\Framework\App\Filesystem');
        $this->databaseMock = $this->basicMock('\Magento\Core\Helper\File\Storage\Database');
        $this->model = $this->objectManager->getObject('Magento\Core\Model\Asset\Plugin\CleanMergedJsCss',
            [
                'database' => $this->databaseMock,
                'filesystem' => $this->filesystemMock,
            ]
        );
    }

    public function testAroundCleanMergedJsCss()
    {
        $callable = function () {
            $this->hasBeenCalled = true;
        };
        $readDir = 'read directory';
        $mergedDir = $readDir .  '/' . \Magento\Framework\View\Asset\Merged::getRelativeDir();

        $readDirectoryMock = $this->basicMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $readDirectoryMock->expects($this->any())->method('getAbsolutePath')->willReturn($readDir);

        $this->databaseMock->expects($this->once())
            ->method('deleteFolder')
            ->with($mergedDir);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR)
            ->willReturn($readDirectoryMock);

        $this->model->aroundCleanMergedJsCss(
            $this->basicMock('\Magento\Framework\View\Asset\MergeService'),
            $callable
        );

        $this->assertTrue($this->hasBeenCalled);
    }
}
