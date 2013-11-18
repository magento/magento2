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
 * @package     Magento_Page
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Page\Block\Html\Head;

/**
 * Link page block
 */
class Link extends \Magento\Core\Block\Template
    implements \Magento\Page\Block\Html\Head\AssetBlock
{
    const VIRTUAL_CONTENT_TYPE = 'link';

    /**
     * Contructor
     *
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\Page\Asset\RemoteFactory $remoteFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\Page\Asset\RemoteFactory $remoteFactory,
        \Magento\Core\Helper\Data $coreData,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $data);
        $this->setAsset(
            $remoteFactory->create(array(
                'url' => (string)$this->getData('url'),
                'contentType' => self::VIRTUAL_CONTENT_TYPE,
            ))
        );
    }

    /**
     * Get block asset
     *
     * @return \Magento\Core\Model\Page\Asset\AssetInterface
     */
    public function getAsset()
    {
        return $this->_getData('asset');
    }
}
