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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Block_Template_Engine_Php implements Mage_Core_Block_Template_EngineInterface
{
    /**
     * @var array
     */
    protected $_blockStack = array();

    /**
     * @var Mage_Core_Block_Template
     */
    protected $_currentBlock;

    /**
     * Include the named PHTML template using the given block as the $this
     * reference, though only public methods will be accessible.
     *
     * @param Mage_Core_Block_Template $block
     * @param string $templateFile
     * @param array $vars
     */
    public function render(Mage_Core_Block_Template $block, $fileName, $vars)
    {
        array_push($this->_blockStack, $block);
        $this->_currentBlock = $block;

        extract ($vars, EXTR_SKIP);
		include $fileName;

        array_pop($this->_blockStack);
        $this->_currentBlock = end($this->_blockStack);
    }

    /**
     * Redirects methods calls to the current block.  This is needed because
     * the templates are included in the context of this engine rather than
     * in the context of the block.
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_currentBlock, $method), $args);
    }
}
