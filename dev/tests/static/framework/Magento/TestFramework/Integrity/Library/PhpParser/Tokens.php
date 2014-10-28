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
 * Parse php code and found dependencies
 *
 */
class Tokens
{
    /**
     * Collect all tokens
     *
     * @var array
     */
    protected $tokens = array();

    /**
     * Collect dependencies
     *
     * @var array
     */
    protected $dependencies = array();

    /**
     * Collect all parsers
     *
     * @var Parser[]
     */
    protected $parsers = array();

    /**
     * Parser factory for creating parsers
     *
     * @var ParserFactory
     */
    protected $parserFactory;

    /**
     * @param string $content
     * @param ParserFactory $parserFactory
     */
    public function __construct($content, ParserFactory $parserFactory)
    {
        $this->tokens = token_get_all($content);
        $this->parserFactory = $parserFactory;
    }

    /**
     * Parse content
     */
    public function parseContent()
    {
        foreach ($this->tokens as $k => $token) {
            foreach ($this->getParsers() as $parser) {
                $parser->parse($token, $k);
            }
        }
    }

    /**
     * Get all parsers
     *
     * @return Parser[]
     */
    protected function getParsers()
    {
        return $this->parserFactory->createParsers($this);
    }

    /**
     * Get parsed dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return array_merge(
            $this->parserFactory->getStaticCalls()->getDependencies($this->parserFactory->getUses()),
            $this->parserFactory->getThrows()->getDependencies($this->parserFactory->getUses())
        );
    }

    /**
     * Return previous token
     *
     * @param int $key
     * @param int $step
     * @return array
     */
    public function getPreviousToken($key, $step = 1)
    {
        return $this->tokens[$key - $step];
    }

    /**
     * Return token code by key
     *
     * @param $key
     * @return null|int
     */
    public function getTokenCodeByKey($key)
    {
        return is_array($this->tokens[$key]) ? $this->tokens[$key][0] : null;
    }

    /**
     * Return token value by key
     *
     * @param $key
     * @return string
     */
    public function getTokenValueByKey($key)
    {
        return is_array($this->tokens[$key]) ? $this->tokens[$key][1] : $this->tokens[$key];
    }
}
