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
 * Parse throws and collect dependencies for it
 *
 */
class Throws implements Parser, DependenciesCollector
{
    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * Collect dependencies
     *
     * @var array
     */
    protected $dependencies = array();

    /**
     * Save throw token key
     *
     * @var array
     */
    protected $throws = array();

    /**
     * @param Tokens $tokens
     */
    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @inheritdoc
     */
    public function parse($token, $key)
    {
        if (is_array($token) && $token[0] == T_THROW) {
            $this->throws[] = $key;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(Uses $uses)
    {
        foreach ($this->throws as $throw) {
            $class = '';
            if ($this->tokens->getTokenCodeByKey($throw + 2) == T_NEW) {
                $step = 4;
                while ($this->tokens->getTokenCodeByKey(
                    $throw + $step
                ) == T_STRING || $this->tokens->getTokenCodeByKey(
                    $throw + $step
                ) == T_NS_SEPARATOR) {
                    $class .= trim($this->tokens->getTokenValueByKey($throw + $step));
                    $step++;
                }
                if ($uses->hasUses()) {
                    $class = $uses->getClassNameWithNamespace($class);
                }
                $this->dependencies[] = $class;
            }
        }

        return $this->dependencies;
    }
}
