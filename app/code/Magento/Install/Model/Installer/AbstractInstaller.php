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
 * @category    Magento
 * @package     Magento_Install
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Abstract installer model
 *
 * @category   Magento
 * @package    Magento_Install
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Model\Installer;

class AbstractInstaller
{
    /**
     * Installer model
     *
     * @var \Magento\Install\Model\Installer
     */
    protected $installer;

    /**
     * @param \Magento\Install\Model\Installer $installer
     */
    public function __construct(\Magento\Install\Model\Installer $installer)
    {
        $this->_installer = $installer;
    }

    /**
     * Installer singleton
     *
     * @var \Magento\Install\Model\Installer
     */
    protected $_installer;

    /**
     * Get installer singleton
     *
     * @return \Magento\Install\Model\Installer
     */
    protected function _getInstaller()
    {
        return $this->_installer;
    }

    /**
     * Validate session storage value (files or db)
     * If empty, will return 'files'
     *
     * @param string $value
     * @return string
     * @throws \Exception
     */
    protected function _checkSessionSave($value)
    {
        if (empty($value)) {
            return 'files';
        }
        if (!in_array($value, array('files', 'db'), true)) {
            throw new \Exception('session_save value must be "files" or "db".');
        }
        return $value;
    }

    /**
     * Validate backend area frontname value.
     * If empty, "backend" will be returned
     *
     * @param string $value
     * @return string
     * @throws \Exception
     */
    protected function _checkBackendFrontname($value)
    {
        if (empty($value)) {
            return 'backend';
        }
        if (!preg_match('/^[a-z]+[a-z0-9_]+$/', $value)) {
            throw new \Exception(
                'backend_frontname value must contain only letters (a-z), numbers (0-9)' .
                ' or underscore(_), first character should be a letter.'
            );
        }
        return $value;
    }
}
