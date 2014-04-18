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
namespace Magento\Cms\Helper\Wysiwyg;

class ImagesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStorageRoot()
    {
        $path = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Filesystem'
        )->getPath(
            \Magento\Framework\App\Filesystem::MEDIA_DIR
        );
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cms\Helper\Wysiwyg\Images'
        );
        $realPath = str_replace('\\', '/', $path);
        $this->assertStringStartsWith($realPath, $helper->getStorageRoot());
    }

    public function testGetCurrentUrl()
    {
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cms\Helper\Wysiwyg\Images'
        );
        $this->assertStringStartsWith('http://localhost/', $helper->getCurrentUrl());
    }
}
