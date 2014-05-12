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
 * Parse static calls and collect dependencies for it
 *
 */
class StaticCalls implements Parser, DependenciesCollector
{
    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * Save static calls token key
     *
     * @var array
     */
    protected $staticCalls = array();

    /**
     * Collect dependencies
     *
     * @var array
     */
    protected $dependencies = array();

    /**
     * @param Tokens $tokens
     */
    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Check if it's foreign dependency
     *
     * @param array $token
     * @return bool
     */
    protected function isTokenClass($token)
    {
        return is_array(
            $token
        ) && !(in_array(
            $token[1],
            array('static', 'self', 'parent')
        ) || preg_match(
            '#^\$#',
            $token[1]
        ));
    }

    /**
     * @inheritdoc
     */
    public function parse($token, $key)
    {
        if (is_array(
            $token
        ) && $token[0] == T_PAAMAYIM_NEKUDOTAYIM && $this->isTokenClass(
            $this->tokens->getPreviousToken($key)
        )
        ) {
            $this->staticCalls[] = $key;
        }
    }

    /**
     * Return class name from token
     *
     * @param int $staticCall
     * @return string
     */
    protected function getClassByStaticCall($staticCall)
    {
        $step = 1;
        $staticClassParts = array();
        while ($this->tokens->getTokenCodeByKey(
            $staticCall - $step
        ) == T_STRING || $this->tokens->getTokenCodeByKey(
            $staticCall - $step
        ) == T_NS_SEPARATOR) {
            $staticClassParts[] = $this->tokens->getTokenValueByKey($staticCall - $step);
            $step++;
        }
        return implode(array_reverse($staticClassParts));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(Uses $uses)
    {
        foreach ($this->staticCalls as $staticCall) {
            $class = $this->getClassByStaticCall($staticCall);
            if ($uses->hasUses()) {
                $class = $uses->getClassNameWithNamespace($class);
            }
            $this->dependencies[] = $class;
        }

        return $this->dependencies;
    }
}
