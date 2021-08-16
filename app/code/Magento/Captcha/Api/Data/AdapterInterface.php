<?php
/**
 * @see       https://github.com/laminas/laminas-captcha for the canonical source repository
 * @copyright https://github.com/laminas/laminas-captcha/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-captcha/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Captcha\Api\Data;

use Laminas\Validator\ValidatorInterface;

/**
 * Generic Captcha adapter interface
 *
 * Each specific captcha implementation should implement this interface
 */
interface AdapterInterface extends ValidatorInterface
{
    /**
     * Generate a new captcha
     *
     * @return string new captcha ID
     */
    public function generate();

    /**
     * Set captcha name
     *
     * @param  string $name
     * @return AdapterInterface
     */
    public function setName($name);

    /**
     * Get captcha name
     *
     * @return string
     */
    public function getName();

    /**
     * Get helper name to use when rendering this captcha type
     *
     * @return string
     */
    public function getHelperName();
}
