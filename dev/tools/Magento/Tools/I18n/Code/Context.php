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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code;

/**
 *  Context
 */
class Context
{
    /**
     * Locale directory
     */
    const LOCALE_DIRECTORY = 'i18n';

    /**#@+
     * Context info
     */
    const CONTEXT_TYPE_MODULE = 'module';

    const CONTEXT_TYPE_THEME = 'theme';

    const CONTEXT_TYPE_LIB = 'lib';

    /**#@-*/

    /**
     * Get context from file path in array(<context type>, <context value>) format
     * - for module: <Namespace>_<module name>
     * - for theme: <area>/<theme name>
     * - for pub: relative path to file
     *
     * @param string $path
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getContextByPath($path)
    {
        if ($value = strstr($path, '/app/code/')) {
            $type = self::CONTEXT_TYPE_MODULE;
            $value = explode('/', $value);
            $value = $value[3] . '_' . $value[4];
        } elseif ($value = strstr($path, '/app/design/')) {
            $type = self::CONTEXT_TYPE_THEME;
            $value = explode('/', $value);
            $value = $value[3] . '/' . $value[4] . '/' . $value[5];
        } elseif ($value = strstr($path, '/lib/web/')) {
            $type = self::CONTEXT_TYPE_LIB;
            $value = ltrim($value, '/');
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid path given: "%s".', $path));
        }
        return array($type, $value);
    }

    /**
     * Get paths by context
     *
     * @param string $type
     * @param array $value
     * @return string
     * @throws \InvalidArgumentException
     */
    public function buildPathToLocaleDirectoryByContext($type, $value)
    {
        switch ($type) {
            case self::CONTEXT_TYPE_MODULE:
                $path = 'app/code/' . str_replace('_', '/', $value);
                break;
            case self::CONTEXT_TYPE_THEME:
                $path = 'app/design/' . $value;
                break;
            case self::CONTEXT_TYPE_LIB:
                $path = 'lib/web';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid context given: "%s".', $type));
        }
        return $path . '/' . self::LOCALE_DIRECTORY . '/';
    }
}
