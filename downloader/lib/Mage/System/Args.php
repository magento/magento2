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
 * @category    Mage
 * @package     Mage_System
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Command-line options parsing class.
 */
class Mage_System_Args
{
    public $flags;
    public $filtered;

    /**
     * Get flags/named options
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Get filtered args
     * @return array
     */
    public function getFiltered()
    {
        return $this->filtered;
    }

    /**
     * Constructor
     * @param array $argv, if false $GLOBALS['argv'] is taken
     * @return void
     */
    public function __construct($source = false)
    {
        $this->flags = array();
        $this->filtered = array();
        
        if(false === $source) {
            $argv = $GLOBALS['argv'];
            array_shift($argv);
        }

        for($i = 0, $iCount = count($argv); $i < $iCount; $i++)
        {
            $str = $argv[$i];

            // --foo
            if(strlen($str) > 2 && substr($str, 0, 2) == '--')
            {
                $str = substr($str, 2);
                $parts = explode('=', $str);
                $this->flags[$parts[0]] = true;

                // Does not have an =, so choose the next arg as its value
                if(count($parts) == 1 && isset($argv[$i + 1]) && preg_match('/^--?.+/', $argv[$i + 1]) == 0)
                {
                    $this->flags[$parts[0]] = $argv[$i + 1];
                    $argv[$i + 1] = null;
                }
                elseif(count($parts) == 2) // Has a =, so pick the second piece
                {
                    $this->flags[$parts[0]] = $parts[1];
                }
            }
            elseif(strlen($str) == 2 && $str[0] == '-') // -a
            {
                $this->flags[$str[1]] = true;                
                if(isset($argv[$i + 1]) && preg_match('/^--?.+/', $argv[$i + 1]) == 0) {
                    $this->flags[$str[1]] = $argv[$i + 1];
                    $argv[$i + 1] = null;
                }
            } else if(!is_null($str)) {
                $this->filtered[] = $str;
            }
        }
    }
}
