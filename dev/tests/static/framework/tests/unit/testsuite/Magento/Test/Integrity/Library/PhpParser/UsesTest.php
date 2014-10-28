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
namespace Magento\Test\Integrity\Library\PhpParser;

use Magento\TestFramework\Integrity\Library\PhpParser\Uses;

/**
 */
class UsesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Uses
     */
    protected $uses;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->uses = new Uses();
    }

    /**
     * Covered hasUses method
     *
     * @dataProvider usesDataProvider
     * @test
     *
     * @param array $tokens
     */
    public function testHasUses($tokens)
    {
        foreach ($tokens as $k => $token) {
            $this->uses->parse($token, $k);
        }
        $this->assertTrue($this->uses->hasUses());
    }

    /**
     * Example tokenizer results
     *
     * @return array
     */
    public function usesDataProvider()
    {
        return array(
            'simple' => array(
                array(
                    0 => array(T_USE, 'use '),
                    1 => array(T_STRING, 'Magento'),
                    2 => array(T_NS_SEPARATOR, '\\'),
                    3 => array(T_STRING, 'Core'),
                    4 => array(T_NS_SEPARATOR, '\\'),
                    5 => array(T_STRING, 'Model'),
                    6 => array(T_NS_SEPARATOR, '\\'),
                    7 => array(T_STRING, 'Object'),
                    8 => ';'
                )
            ),
            'several_simple' => array(
                array(
                    0 => array(T_USE, 'use '),
                    1 => array(T_STRING, 'Magento'),
                    2 => array(T_NS_SEPARATOR, '\\'),
                    3 => array(T_STRING, 'Core'),
                    4 => array(T_NS_SEPARATOR, '\\'),
                    5 => array(T_STRING, 'Model'),
                    6 => array(T_NS_SEPARATOR, '\\'),
                    7 => array(T_STRING, 'Object'),
                    8 => ';',
                    9 => array(T_USE, 'use '),
                    10 => array(T_STRING, 'Magento'),
                    11 => array(T_NS_SEPARATOR, '\\'),
                    12 => array(T_STRING, 'Core'),
                    13 => array(T_NS_SEPARATOR, '\\'),
                    14 => array(T_STRING, 'Model'),
                    15 => array(T_NS_SEPARATOR, '\\'),
                    16 => array(T_STRING, 'Object2 '),
                    17 => array(T_AS, 'as '),
                    18 => array(T_STRING, 'OtherObject'),
                    19 => ';'
                )
            ),
            'several_with_comma_separate' => array(
                array(
                    0 => array(T_USE, 'use '),
                    1 => array(T_STRING, 'Magento'),
                    2 => array(T_NS_SEPARATOR, '\\'),
                    3 => array(T_STRING, 'Core'),
                    4 => array(T_NS_SEPARATOR, '\\'),
                    5 => array(T_STRING, 'Model'),
                    6 => array(T_NS_SEPARATOR, '\\'),
                    7 => array(T_STRING, 'Object'),
                    8 => ',',
                    9 => array(T_STRING, 'Magento'),
                    10 => array(T_NS_SEPARATOR, '\\'),
                    11 => array(T_STRING, 'Core'),
                    12 => array(T_NS_SEPARATOR, '\\'),
                    13 => array(T_STRING, 'Model'),
                    14 => array(T_NS_SEPARATOR, '\\'),
                    15 => array(T_STRING, 'Object2 '),
                    16 => array(T_AS, 'as '),
                    17 => array(T_STRING, 'OtherObject'),
                    18 => ';'
                )
            )
        );
    }

    /**
     * Covered getClassNameWithNamespace for global classes
     *
     * @test
     */
    public function testGetClassNameWithNamespaceForGlobalClass()
    {
        $this->assertEquals(
            '\Magento\Core\Model\Object2',
            $this->uses->getClassNameWithNamespace('\Magento\Core\Model\Object2')
        );
    }

    /**
     * Covered getClassNameWithNamespace
     *
     * @test
     * @dataProvider classNamesDataProvider
     */
    public function testGetClassNameWithNamespace($className, $tokens)
    {
        foreach ($tokens as $k => $token) {
            $this->uses->parse($token, $k);
        }

        $this->assertEquals('Magento\Core\Model\Object2', $this->uses->getClassNameWithNamespace($className));
    }

    /**
     * Return different uses token list and class name
     *
     * @return array
     */
    public function classNamesDataProvider()
    {
        return array(
            'class_from_uses' => array(
                'Object2',
                array(
                    0 => array(T_USE, 'use '),
                    1 => array(T_STRING, 'Magento'),
                    2 => array(T_NS_SEPARATOR, '\\'),
                    3 => array(T_STRING, 'Core'),
                    4 => array(T_NS_SEPARATOR, '\\'),
                    5 => array(T_STRING, 'Model'),
                    6 => array(T_NS_SEPARATOR, '\\'),
                    7 => array(T_STRING, 'Object2'),
                    8 => ';'
                )
            ),
            'class_from_uses_with_as' => array(
                'ObjectOther',
                array(
                    0 => array(T_USE, 'use '),
                    1 => array(T_STRING, 'Magento'),
                    2 => array(T_NS_SEPARATOR, '\\'),
                    3 => array(T_STRING, 'Core'),
                    4 => array(T_NS_SEPARATOR, '\\'),
                    5 => array(T_STRING, 'Model'),
                    6 => array(T_NS_SEPARATOR, '\\'),
                    7 => array(T_STRING, 'Object2 '),
                    8 => array(T_AS, 'as '),
                    9 => array(T_STRING, 'ObjectOther'),
                    10 => ';'
                )
            )
        );
    }
}
