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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Auth session model
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Model;

class Session extends \Magento\Core\Model\Session\AbstractSession
{
    /**
     * Connect data
     *
     * @var \Magento\Connect\Helper\Data
     */
    protected $_connectData;

    /**
     * @param \Magento\Core\Model\Session\Context $context
     * @param \Magento\Connect\Helper\Data $connectData
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Session\Context $context,
        \Magento\Connect\Helper\Data $connectData,
        array $data = array()
    ) {
        $this->_connectData = $connectData;
        parent::__construct($context, $data);
        $this->init('adminhtml');
    }

    /**
    * Retrieve parameters of extension from session.
    * Compatible with old version extension info file.
    *
    * @return array
    */
    public function getCustomExtensionPackageFormData()
    {
        $data = $this->getData('custom_extension_package_form_data');
        /* convert Maintainers to Authors */
        if (!isset($data['authors']) || count($data['authors']) == 0) {
            if (isset($data['maintainers'])) {
                $data['authors']['name'] = array();
                $data['authors']['user'] = array();
                $data['authors']['email'] = array();
                foreach ($data['maintainers']['name'] as $i => $name) {
                    if (!$data['maintainers']['name'][$i]
                        && !$data['maintainers']['handle'][$i]
                        && !$data['maintainers']['email'][$i]
                    ) {
                        continue;
                    }
                    array_push($data['authors']['name'], $data['maintainers']['name'][$i]);
                    array_push($data['authors']['user'], $data['maintainers']['handle'][$i]);
                    array_push($data['authors']['email'], $data['maintainers']['email'][$i]);
                }
                // Convert channel from previous version for entire package
                $helper = $this->_connectData;
                if (isset($data['channel'])) {
                    $data['channel'] = $helper->convertChannelFromV1x($data['channel']);
                }
                // Convert channel from previous version for each required package
                $nRequiredPackages = count($data['depends']['package']['channel']);
                for ($i = 0; $i < $nRequiredPackages; $i++) {
                    $channel = $data['depends']['package']['channel'][$i];
                    if ($channel) {
                        $data['depends']['package']['channel'][$i] = $helper->convertChannelFromV1x($channel);
                    }
                }
            }
        }

        /* convert Release version to Version */
        if (!isset($data['version'])) {
            if (isset($data['release_version'])) {
                $data['version'] = $data['release_version'];
            }
        }
        /* convert Release stability to Stability */
        if (!isset($data['stability'])) {
            if (isset($data['release_stability'])) {
                $data['stability'] = $data['release_stability'];
            }
        }
        /* convert contents */
        if (!isset($data['contents']['target'])) {
            $data['contents']['target'] = $data['contents']['role'];
        }
        return $data;
    }

}
