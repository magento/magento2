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
namespace Magento\Core\Model\App;

use Magento\Framework\App\State;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $mode
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($mode)
    {
        $model = new \Magento\Framework\App\State(
            $this->getMockForAbstractClass('Magento\Framework\Config\ScopeInterface', array(), '', false),
            time(),
            $mode
        );
        $this->assertEquals($mode, $model->getMode());
    }

    /**
     * @return array
     */
    public static function constructorDataProvider()
    {
        return array(
            'default mode' => array(\Magento\Framework\App\State::MODE_DEFAULT),
            'production mode' => array(\Magento\Framework\App\State::MODE_PRODUCTION),
            'developer mode' => array(\Magento\Framework\App\State::MODE_DEVELOPER)
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unknown application mode: unknown mode
     */
    public function testConstructorException()
    {
        new \Magento\Framework\App\State(
            $this->getMockForAbstractClass('Magento\Framework\Config\ScopeInterface', array(), '', false),
            time(),
            "unknown mode"
        );
    }
}
