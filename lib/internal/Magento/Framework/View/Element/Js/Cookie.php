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
namespace Magento\Framework\View\Element\Js;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Cookie extends Template
{
    /**
     * Session config
     *
     * @var ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var \Magento\Framework\Validator\Ip
     */
    protected $ipValidator;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigInterface $cookieConfig
     * @param \Magento\Framework\Validator\Ip $ipValidator
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $cookieConfig,
        \Magento\Framework\Validator\Ip $ipValidator,
        array $data = array()
    ) {
        $this->sessionConfig = $cookieConfig;
        $this->ipValidator = $ipValidator;
        parent::__construct($context, $data);
    }

    /**
     * Get configured cookie domain
     *
     * @return string
     */
    public function getDomain()
    {
        $domain = $this->sessionConfig->getCookieDomain();

        if ($this->ipValidator->isValid($domain)) {
            return $domain;
        }

        if (!empty($domain[0]) && $domain[0] !== '.') {
            $domain = '.' . $domain;
        }
        return $domain;
    }

    /**
     * Get configured cookie path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->sessionConfig->getCookiePath();
    }
}
