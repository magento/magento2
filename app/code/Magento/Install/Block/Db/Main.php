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

/**
 * Database config installation block
 */
namespace Magento\Install\Block\Db;

class Main extends \Magento\Framework\View\Element\Template
{
    /**
     * Array of Database blocks keyed by name
     *
     * @var array
     */
    protected $_databases = array();

    /**
     * Install installer config
     *
     * @var \Magento\Install\Model\Installer\Config
     */
    protected $_installerConfig = null;

    /**
     * Install installer config
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Install\Model\Installer\Config $installerConfig
     * @param \Magento\Framework\Session\Generic $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Install\Model\Installer\Config $installerConfig,
        \Magento\Framework\Session\Generic $session,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_installerConfig = $installerConfig;
        $this->_session = $session;
    }

    /**
     * Adding customized database block template for database model type
     *
     * @param  string $type database type
     * @param  string $block database block type
     * @param  string $template
     * @return $this
     */
    public function addDatabaseBlock($type, $block, $template)
    {
        $this->_databases[$type] = array('block' => $block, 'template' => $template, 'instance' => null);

        return $this;
    }

    /**
     * Retrieve database block by type
     *
     * @param  string $type database model type
     * @return bool|\Magento\Framework\View\Element\Template
     */
    public function getDatabaseBlock($type)
    {
        $block = false;
        if (isset($this->_databases[$type])) {
            if ($this->_databases[$type]['instance']) {
                $block = $this->_databases[$type]['instance'];
            } else {
                $block = $this->getLayout()->createBlock(
                    $this->_databases[$type]['block']
                )->setTemplate(
                    $this->_databases[$type]['template']
                )->setIdPrefix(
                    $type
                );
                $this->_databases[$type]['instance'] = $block;
            }
        }
        return $block;
    }

    /**
     * Retrieve database blocks
     *
     * @return array
     */
    public function getDatabaseBlocks()
    {
        $databases = array();
        foreach (array_keys($this->_databases) as $type) {
            $databases[] = $this->getDatabaseBlock($type);
        }
        return $databases;
    }

    /**
     * Retrieve configuration form data object
     *
     * @return \Magento\Framework\Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $data = $this->_session->getConfigData(true);
            if (empty($data)) {
                $data = $this->_installerConfig->getFormData();
            } else {
                $data = new \Magento\Framework\Object($data);
            }
            $this->setFormData($data);
        }
        return $data;
    }
}
