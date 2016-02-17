<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\View;

use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Service manager configuration for i18n view helpers.
 */
class HelperConfig implements ConfigInterface
{
    /**
     * Pre-aliased view helpers
     *
     * @var array
     */
    protected $invokables = array(
        'currencyformat'  => 'Zend\I18n\View\Helper\CurrencyFormat',
        'dateformat'      => 'Zend\I18n\View\Helper\DateFormat',
        'numberformat'    => 'Zend\I18n\View\Helper\NumberFormat',
        'plural'          => 'Zend\I18n\View\Helper\Plural',
        'translate'       => 'Zend\I18n\View\Helper\Translate',
        'translateplural' => 'Zend\I18n\View\Helper\TranslatePlural',
    );

    /**
     * Configure the provided service manager instance with the configuration
     * in this class.
     *
     * @param  ServiceManager $serviceManager
     * @return void
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        foreach ($this->invokables as $name => $service) {
            $serviceManager->setInvokableClass($name, $service);
        }
    }
}
