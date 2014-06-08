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

namespace Mtf\App\State;

use Magento\Core\Test\Fixture\Config;

/**
 * Class State1
 * Example Application State class
 */
class State1 extends AbstractState
{
    /**
     * Configuration fixture
     *
     * @var Config
     */
    protected $config;

    /**
     * @construct
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        parent::apply();
        $this->config->switchData('app_state1_configuration');
        $this->config->persist();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Configuration Profile #1';
    }
}
