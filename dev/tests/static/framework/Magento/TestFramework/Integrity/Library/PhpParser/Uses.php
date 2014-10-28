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
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Parse uses block
 *
 */
class Uses implements Parser
{
    /**
     * Flag for parse use block
     *
     * @var bool
     */
    protected $parseUse = false;

    /**
     * Collect all uses
     *
     * @var array
     */
    protected $uses = array();

    /**
     * Check if uses present in content
     *
     * @return bool
     */
    public function hasUses()
    {
        return !empty($this->uses);
    }

    /**
     * Create empty uses in collection
     */
    protected function createEmptyItem()
    {
        $this->uses[] = '';
    }

    /**
     * Return class name with namespace
     *
     * @param string $class
     * @return string
     */
    public function getClassNameWithNamespace($class)
    {
        if (preg_match('#^\\\\#', $class)) {
            return $class;
        }

        preg_match('#^([A-Za-z0-9_]+)(.*)$#', $class, $match);
        foreach ($this->uses as $use) {
            if (preg_match('#^([A-Za-z0-9_\\\\]+)\s+as\s+(.*)$#', $use, $useMatch) && $useMatch[2] == $match[1]) {
                $class = $useMatch[1] . $match[2];
                break;
            }
            $packages = explode('\\', $use);
            end($packages);
            $lastPackageKey = key($packages);
            if ($packages[$lastPackageKey] == $match[1]) {
                $class = $use . $match[2];
            }
        }
        return $class;
    }

    /**
     * Append part of uses into last item
     *
     * @param string $value
     */
    protected function appendToLast($value)
    {
        end($this->uses);
        $this->uses[key($this->uses)] = ltrim($this->uses[key($this->uses)] . $value);
    }

    /**
     * Check flag parse
     *
     * @return bool
     */
    protected function isParseInProgress()
    {
        return $this->parseUse;
    }

    /**
     * Start parse
     */
    protected function stopParse()
    {
        $this->parseUse = false;
    }

    /**
     * Stop parse
     */
    protected function startParse()
    {
        $this->parseUse = true;
    }

    /**
     * @inheritdoc
     */
    public function parse($token, $key)
    {
        if (is_array($token)) {
            if ($this->isParseInProgress()) {
                $this->appendToLast($token[1]);
            }
            if (T_USE == $token[0]) {
                $this->startParse();
                $this->createEmptyItem();
            }
        } else {
            if ($this->isParseInProgress()) {
                if ($token == ';') {
                    $this->stopParse();
                }
                if ($token == ',') {
                    $this->createEmptyItem();
                }
            }
        }
    }
}
