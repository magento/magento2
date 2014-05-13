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
 * Class know how create any parser
 *
 */
class ParserFactory
{
    /**
     * @var Parser[]
     */
    protected $parsers = array();

    /**
     * @var Uses
     */
    protected $uses;

    /**
     * @var StaticCalls
     */
    protected $staticCalls;

    /**
     * @var Throws
     */
    protected $throws;

    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * Return all parsers
     *
     * @param Tokens $tokens
     * @return Parser[]
     */
    public function createParsers(Tokens $tokens)
    {
        if (empty($this->parsers)) {
            $this->parsers = array(
                $this->uses = new Uses(),
                $this->staticCalls = new StaticCalls($tokens),
                $this->throws = new Throws($tokens)
            );
        }
        return $this->parsers;
    }

    /**
     * Get uses
     *
     * @return Uses
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Get static calls
     *
     * @return StaticCalls
     */
    public function getStaticCalls()
    {
        return $this->staticCalls;
    }

    /**
     * Get throws
     *
     * @return Throws
     */
    public function getThrows()
    {
        return $this->throws;
    }
}
