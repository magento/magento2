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
namespace Magento\Framework\Image;

use Magento\Framework\ObjectManager;

class Factory
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @param ObjectManager $objectManager
     * @param AdapterFactory $adapterFactory
     */
    public function __construct(ObjectManager $objectManager, AdapterFactory $adapterFactory)
    {
        $this->objectManager = $objectManager;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Create instance of \Magento\Framework\Image
     *
     * @param string|null $fileName
     * @param string|null $adapterName
     * @return \Magento\Framework\Image
     */
    public function create($fileName = null, $adapterName = null)
    {
        $adapter = $this->adapterFactory->create($adapterName);
        return $this->objectManager->create('Magento\Framework\Image', array('adapter' => $adapter, 'fileName' => $fileName));
    }
}
