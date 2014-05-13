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
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Magento\Framework\App\State;

/**
 * Oyejorge adapter model
 */
class OyejorgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Oyejorge
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\Css\PreProcessor\Adapter\Oyejorge');
        $this->state = $objectManager->get('Magento\Framework\App\State');
    }

    public function testProcess()
    {
        $sourceFilePath = realpath(__DIR__ . '/../_files/oyejorge.less');
        $expectedCss = ($this->state->getMode() === State::MODE_DEVELOPER)
            ? file_get_contents(__DIR__ . '/../_files/oyejorge_dev.css')
            : file_get_contents(__DIR__ . '/../_files/oyejorge.css');
        $this->assertEquals($expectedCss, $this->model->process($sourceFilePath));
    }
}
