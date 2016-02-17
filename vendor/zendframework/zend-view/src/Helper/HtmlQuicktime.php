<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

class HtmlQuicktime extends AbstractHtmlElement
{
    /**
     * Default file type for a movie applet
     */
    const TYPE = 'video/quicktime';

    /**
     * Object classid
     */
    const ATTRIB_CLASSID  = 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B';

    /**
     * Object Codebase
     */
    const ATTRIB_CODEBASE = 'http://www.apple.com/qtactivex/qtplugin.cab';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $attribs = array('classid' => self::ATTRIB_CLASSID, 'codebase' => self::ATTRIB_CODEBASE);

    /**
     * Output a quicktime movie object tag
     *
     * @param  string $data    The quicktime file
     * @param  array  $attribs Attribs for the object tag
     * @param  array  $params  Params for in the object tag
     * @param  string $content Alternative content
     * @return string
     */
    public function __invoke($data, array $attribs = array(), array $params = array(), $content = null)
    {
        // Attrs
        $attribs = array_merge($this->attribs, $attribs);

        // Params
        $params = array_merge(array('src' => $data), $params);

        $htmlObject = $this->getView()->plugin('htmlObject');
        return $htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
