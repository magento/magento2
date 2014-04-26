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
 * Class with simple substitution parameters to values
 */
class Simple implements RuleInterface
{
    /**
     * Optional params for rule
     *
     * @var array
     */
    protected $optionalParams;

    /**
     * Pattern for a simple rule
     *
     * @var string
     */
    protected $pattern;

    /**
     * Constructor
     *
     * @param string $pattern
     * @param array $optionalParams
     */
    public function __construct($pattern, array $optionalParams = array())
    {
        $this->pattern = $pattern;
        $this->optionalParams = $optionalParams;
    }

    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params array of parameters
     * @return array folders to perform a search
     * @throws \InvalidArgumentException
     */
    public function getPatternDirs(array $params)
    {
        $pattern = $this->pattern;
        if (preg_match_all('/<([a-zA-Z\_]+)>/', $pattern, $matches)) {
            foreach ($matches[1] as $placeholder) {
                if (empty($params[$placeholder])) {
                    if (in_array($placeholder, $this->optionalParams)) {
                        return array();
                    } else {
                        throw new \InvalidArgumentException("Required parameter '{$placeholder}' was not passed");
                    }
                }
                $pattern = str_replace('<' . $placeholder . '>', $params[$placeholder], $pattern);
            }
        }
        return array($pattern);
    }
}
