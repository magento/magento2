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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Pear;

class Registry //extends PEAR_Registry
{
    /*    function _initializeDepDB()
        {
            if (!isset($this->_dependencyDB)) {
                static $initializing = false;
                if (!$initializing) {
                    $initializing = true;
                    if (!$this->_config) { // never used?
                        if (OS_WINDOWS) {
                            $file = 'pear.ini';
                        } else {
                            $file = '.pearrc';
                        }
                        $this->_config = &new PEAR_Config($this->statedir . '/' .
                            $file, '-'); // NO SYSTEM INI FILE
                        $this->_config->setRegistry($this);
                        $this->_config->set('php_dir', $this->install_dir);
                    }
                    $this->_dependencyDB = &PEAR_DependencyDB::singleton($this->_config);
                    if (PEAR::isError($this->_dependencyDB)) {
                        // attempt to recover by removing the dep db
                        if (file_exists($this->_config->get('php_dir', null, 'pear.php.net') .
                            '/' . '.depdb')) {
                            @unlink($this->_config->get('php_dir', null, 'pear.php.net') .
                                '/' . '.depdb');
                        }
                        $this->_dependencyDB = &PEAR_DependencyDB::singleton($this->_config);
                        if (PEAR::isError($this->_dependencyDB)) {
                            echo $this->_dependencyDB->getMessage();
                            echo 'Unrecoverable error';
                            exit(1);
                        }
                    }
                    $initializing = false;
                }
            }
        }*/
}
