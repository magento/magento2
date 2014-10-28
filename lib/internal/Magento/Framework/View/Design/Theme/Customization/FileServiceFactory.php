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
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme customization files factory
 */
class FileServiceFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param ConfigInterface $config
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, ConfigInterface $config)
    {
        $this->_objectManager = $objectManager;
        $this->_types = $config->getFileTypes();
    }

    /**
     * Create new instance
     *
     * @param string $type
     * @param array $data
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface
     * @throws \InvalidArgumentException
     */
    public function create($type, array $data = array())
    {
        if (empty($this->_types[$type])) {
            throw new \InvalidArgumentException('Unsupported file type');
        }
        $fileService = $this->_objectManager->get($this->_types[$type], array($data));
        if (!$fileService instanceof \Magento\Framework\View\Design\Theme\Customization\FileInterface) {
            throw new \InvalidArgumentException('Service don\'t implement interface');
        }
        return $fileService;
    }
}
