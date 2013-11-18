<?php
/**
 * Magento filesystem zlib stream mode
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Stream\Mode;

class Zlib extends \Magento\Filesystem\Stream\Mode
{
    /**
     * Compression ratio
     *
     * @var int
     */
    protected $_ratio = 1;

    /**
     * Compression strategy
     *
     * @var string
     */
    protected $_strategy = '';

    /**
     * @param string $mode
     */
    public function __construct($mode)
    {
        $searchPattern = '/(r|w|a|x|c)(b)?(\+)?(\d*)(f|h)?/';
        preg_match($searchPattern, $mode, $ratios);
        if (count($ratios) > 4 && $ratios[4]) {
            $this->_ratio = (int)$ratios[4];
        }
        if (count($ratios) == 6) {
            $this->_strategy = $ratios[5];
        }
        $mode = preg_replace($searchPattern, '\1\2\3', $mode);
        parent::__construct($mode);
    }

    /**
     * Get compression ratio
     *
     * @return int
     */
    public function getRatio()
    {
        return $this->_ratio;
    }

    /**
     * Get compression strategy
     *
     * @return null|string
     */
    public function getStrategy()
    {
        return $this->_strategy;
    }
}
