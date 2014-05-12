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
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Simple Test
 *
 */
class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Required parameter 'required_parameter' was not passed
     */
    public function testGetPatternDirsException()
    {
        $model = new Simple('<required_parameter> other text');
        $model->getPatternDirs(array());
    }

    /**
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($pattern, $optionalParameter = null, $expectedResult = null)
    {
        $params = array('optional_parameter' => $optionalParameter, 'required_parameter' => 'required_parameter');
        $model = new Simple($pattern, array('optional_parameter'));

        $this->assertEquals($expectedResult, $model->getPatternDirs($params));
    }

    /**
     * @return array
     */
    public function getPatternDirsDataProvider()
    {
        $patternOptional = '<optional_parameter> <required_parameter> other text';
        $patternNoOptional = '<required_parameter> other text';

        return array(
            'no optional param passed' => array($patternOptional, null, array()),
            'no optional param in pattern' => array(
                $patternNoOptional,
                'optional_parameter',
                array('required_parameter other text')
            ),
            'optional params in pattern and passed' => array(
                $patternOptional,
                'optional_parameter',
                array('optional_parameter required_parameter other text')
            )
        );
    }
}
