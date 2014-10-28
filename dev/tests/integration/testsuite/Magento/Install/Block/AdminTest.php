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
namespace Magento\Install\Block;

class AdminTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtml()
    {
        $preserve = array(
            'username' => 'admin',
            'email' => 'admin@example.com',
            'firstname' => 'First',
            'lastname' => 'Last'
        );
        $omit = array('password' => 'password_with_1_number', 'password_confirmation' => 'password_with_1_number');

        /** @var $session \Magento\Framework\Session\Generic */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Install\Model\Session');
        $session->setAdminData(array_merge($preserve, $omit));

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('install');

        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Framework\View\Layout');
        /** @var $block \Magento\Install\Block\Admin */
        $block = $layout->createBlock('Magento\Install\Block\Admin');
        $output = $block->toHtml();

        $this->assertEmpty($session->getAdminData());
        // form elements must be present with values
        foreach ($preserve as $key => $value) {
            $this->assertSelectCount(sprintf('input[name=admin[%s]][value=%s]', $key, $value), 1, $output);
        }
        // form elements must be present without values
        foreach ($omit as $key => $value) {
            $this->assertSelectCount(sprintf('input[name=admin[%s]]', $key), 1, $output);
            $this->assertSelectCount(sprintf('input[name=admin[%s]][value=%s]', $key, $value), 0, $output);
        }
    }
}
