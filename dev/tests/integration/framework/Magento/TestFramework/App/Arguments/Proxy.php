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
namespace Magento\TestFramework\App\Arguments;

/**
 * Proxy class for \Magento\Framework\App\Arguments
 */
class Proxy extends \Magento\Framework\App\Arguments
{
    /**
     * Proxied instance
     *
     * @var \Magento\Framework\App\Arguments
     */
    protected $subject;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\App\Arguments $subject
     */
    public function __construct(\Magento\Framework\App\Arguments $subject)
    {
        $this->setSubject($subject);
    }

    /**
     * Set new subject to be proxied
     *
     * @param \Magento\Framework\App\Arguments $subject
     */
    public function setSubject(\Magento\Framework\App\Arguments $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($connectionName)
    {
        return $this->subject->getConnection($connectionName);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
        return $this->subject->getConnections();
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->subject->getResources();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheFrontendSettings()
    {
        return $this->subject->getCacheFrontendSettings();
    }

    /**
     * Retrieve identifier of a cache frontend, configured to be used for a cache type
     *
     * @param string $cacheType Cache type identifier
     * @return string|null
     */
    public function getCacheTypeFrontendId($cacheType)
    {
        return $this->subject->getCacheTypeFrontendId($cacheType);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key = null, $defaultValue = null)
    {
        return $this->subject->get($key, $defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function reload()
    {
        return $this->subject->reload();
    }
}
