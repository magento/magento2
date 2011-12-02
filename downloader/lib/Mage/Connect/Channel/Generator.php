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
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Connect_Channel_Generator extends Mage_Xml_Generator
{
    protected $_file      = 'channel.xml';
    protected $_generator = null;

    public function __construct($file='')
    {
        if ($file) {
            $this->_file = $file;
        }
        return $this;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function getGenerator()
    {
        if (is_null($this->_generator)) {
            $this->_generator = new Mage_Xml_Generator();
        }
        return $this->_generator;
    }

    /**
     * @param array $content
     */
    public function save($content)
    {
        $xmlContent = $this->getGenerator()
        ->arrayToXml($content)
        ->save($this->getFile());
        return $this;
    }
}
