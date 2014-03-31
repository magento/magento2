<?php
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

/**
 * Magento\Downloadable\Controller\Adminhtml\Downloadable\File
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
 * @magentoAppArea adminhtml
 */
class FileTest extends \Magento\Backend\Utility\Controller
{
    public function testUploadAction()
    {
        copy(dirname(__DIR__) . '/_files/sample.txt', dirname(__DIR__) . '/_files/sample.tmp');
        $_FILES = array(
            'samples' => array(
                'name' => 'sample.txt',
                'type' => 'text/plain',
                'tmp_name' => dirname(__DIR__) . '/_files/sample.tmp',
                'error' => 0,
                'size' => 0
            )
        );

        $this->dispatch('backend/admin/downloadable_file/upload/type/samples');
        $body = $this->getResponse()->getBody();
        $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Helper\Data'
        )->jsonDecode(
            $body
        );
        $this->assertEquals(0, $result['error']);
    }
}
