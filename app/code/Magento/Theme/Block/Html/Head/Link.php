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

namespace Magento\Theme\Block\Html\Head;

/**
 * Link page block
 */
class Link extends \Magento\View\Element\Template implements AssetBlockInterface
{
    /**
     * Virtual content type
     */
    const VIRTUAL_CONTENT_TYPE = 'link';

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\View\Asset\RemoteFactory $remoteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\View\Asset\RemoteFactory $remoteFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
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
     * @return \Magento\View\Asset\AssetInterface
     */
    public function getAsset()
    {
        return $this->_getData('asset');
    }
}
