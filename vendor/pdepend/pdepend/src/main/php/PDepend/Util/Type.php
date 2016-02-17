<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\Util;

/**
 * Utility class that can be used to detect simpl scalars or internal types.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
final class Type
{
    /**
     * Constants for valid php data types.
     */
    const PHP_TYPE_ARRAY   = 'array',
          PHP_TYPE_BOOLEAN = 'boolean',
          PHP_TYPE_FLOAT   = 'float',
          PHP_TYPE_INTEGER = 'integer',
          PHP_TYPE_STRING  = 'string';
    /**
     * Constants with valid php data type identifiers.
     */
    const IMAGE_ARRAY    = 'array',
          IMAGE_BOOL     = 'bool',
          IMAGE_BOOLEAN  = 'boolean',
          IMAGE_DOUBLE   = 'double',
          IMAGE_FLOAT    = 'float',
          IMAGE_INT      = 'int',
          IMAGE_INTEGER  = 'integer',
          IMAGE_MIXED    = 'mixed',
          IMAGE_REAL     = 'real',
          IMAGE_RESOURCE = 'resource',
          IMAGE_OBJECT   = 'object',
          IMAGE_STRING   = 'string',
          IMAGE_STDCLASS = 'stdclass',
          IMAGE_VOID     = 'void';

    /**
     * Constants with the metaphone representation of multiple php data types.
     */
    const IMAGE_METAPHONE_ARRAY        = 'AR',
          IMAGE_METAPHONE_BOOL         = 'BL',
          IMAGE_METAPHONE_BOOLEAN      = 'BLN',
          IMAGE_METAPHONE_DOUBLE       = 'TBL',
          IMAGE_METAPHONE_FLOAT        = 'FLT',
          IMAGE_METAPHONE_INT          = 'INT',
          IMAGE_METAPHONE_INTEGER      = 'INTJR',
          IMAGE_METAPHONE_MIXED        = 'MKST',
          IMAGE_METAPHONE_REAL         = 'RL',
          IMAGE_METAPHONE_RESOURCE     = 'RSRS',
          IMAGE_METAPHONE_OBJECT       = 'OBJKT',
          IMAGE_METAPHONE_STRING       = 'STRNK',
          IMAGE_METAPHONE_STDCLASS     = 'STTKLS',
          IMAGE_METAPHONE_UNKNOWN      = 'UNKNN',
          IMAGE_METAPHONE_UNKNOWN_TYPE = 'UNKNNTP';

    /**
     * Constants with the soundex representation of multiple php data types.
     */
    const IMAGE_SOUNDEX_ARRAY        = 'A600',
          IMAGE_SOUNDEX_BOOL         = 'B450',
          IMAGE_SOUNDEX_BOOLEAN      = 'B400',
          IMAGE_SOUNDEX_DOUBLE       = 'D140',
          IMAGE_SOUNDEX_FLOAT        = 'F430',
          IMAGE_SOUNDEX_INT          = 'I530',
          IMAGE_SOUNDEX_INTEGER      = 'I532',
          IMAGE_SOUNDEX_MIXED        = 'M230',
          IMAGE_SOUNDEX_REAL         = 'R400',
          IMAGE_SOUNDEX_RESOURCE     = 'R262',
          IMAGE_SOUNDEX_OBJECT       = 'O122',
          IMAGE_SOUNDEX_STRING       = 'S365',
          IMAGE_SOUNDEX_STDCLASS     = 'S324',
          IMAGE_SOUNDEX_UNKNOWN      = 'U525';
 
    /**
     * Constants for other types/keywords frequently used.
     */
    const IMAGE_OTHER_NULL         = 'null',
          IMAGE_OTHER_FALSE        = 'false',
          IMAGE_OTHER_TRUE         = 'true',
          IMAGE_OTHER_UNKNOWN      = 'unknown',
          IMAGE_OTHER_UNKNOWN_TYPE = 'unknown_type';

    /**
     * This property contains a mapping between a unified lower case type name
     * and the corresponding PHP extension that declares this type.
     *
     * @var array(string=>string)
     */
    private static $typeNameToExtension = null;

    /**
     * Hash with all internal namespaces/extensions. Key and value are identical
     * and contain the name of the extension.
     *
     * @var   array(string=>string)
     * @since 0.9.10
     */
    private static $internalNamespaces = null;

    /**
     * List of scalar php types.
     *
     * @var array(string)
     */
    private static $scalarTypes = array(
        self::IMAGE_ARRAY                   =>  true,
        self::IMAGE_BOOL                    =>  true,
        self::IMAGE_BOOLEAN                 =>  true,
        self::IMAGE_DOUBLE                  =>  true,
        self::IMAGE_FLOAT                   =>  true,
        self::IMAGE_INT                     =>  true,
        self::IMAGE_INTEGER                 =>  true,
        self::IMAGE_MIXED                   =>  true,
        self::IMAGE_REAL                    =>  true,
        self::IMAGE_RESOURCE                =>  true,
        self::IMAGE_OBJECT                  =>  true,
        self::IMAGE_STRING                  =>  true,
        self::IMAGE_STDCLASS                =>  true,
        self::IMAGE_VOID                    =>  true,
        self::IMAGE_OTHER_NULL              =>  true,
        self::IMAGE_OTHER_FALSE             =>  true,
        self::IMAGE_OTHER_TRUE              =>  true,
        self::IMAGE_OTHER_UNKNOWN           =>  true,
        self::IMAGE_OTHER_UNKNOWN_TYPE      =>  true,
        self::IMAGE_METAPHONE_ARRAY         =>  true,
        self::IMAGE_METAPHONE_BOOL          =>  true,
        self::IMAGE_METAPHONE_BOOLEAN       =>  true,
        self::IMAGE_METAPHONE_DOUBLE        =>  true,
        self::IMAGE_METAPHONE_FLOAT         =>  true,
        self::IMAGE_METAPHONE_INT           =>  true,
        self::IMAGE_METAPHONE_INTEGER       =>  true,
        self::IMAGE_METAPHONE_MIXED         =>  true,
        self::IMAGE_METAPHONE_OBJECT        =>  true,
        self::IMAGE_METAPHONE_REAL          =>  true,
        self::IMAGE_METAPHONE_RESOURCE      =>  true,
        self::IMAGE_METAPHONE_STRING        =>  true,
        self::IMAGE_METAPHONE_STDCLASS      =>  true,
        self::IMAGE_METAPHONE_UNKNOWN       =>  true,
        self::IMAGE_METAPHONE_UNKNOWN_TYPE  =>  true,
        self::IMAGE_SOUNDEX_ARRAY           =>  true,
        self::IMAGE_SOUNDEX_BOOL            =>  true,
        self::IMAGE_SOUNDEX_BOOLEAN         =>  true,
        self::IMAGE_SOUNDEX_DOUBLE          =>  true,
        self::IMAGE_SOUNDEX_FLOAT           =>  true,
        self::IMAGE_SOUNDEX_INT             =>  true,
        self::IMAGE_SOUNDEX_INTEGER         =>  true,
        self::IMAGE_SOUNDEX_MIXED           =>  true,
        self::IMAGE_SOUNDEX_REAL            =>  true,
        self::IMAGE_SOUNDEX_RESOURCE        =>  true,
        self::IMAGE_SOUNDEX_OBJECT          =>  true,
        self::IMAGE_SOUNDEX_STRING          =>  true,
        self::IMAGE_SOUNDEX_STDCLASS        =>  true,
        self::IMAGE_SOUNDEX_UNKNOWN         =>  true,
    );

    /**
     * List of primitive php types.
     *
     * @var array(string=>string)
     */
    private static $primitiveTypes = array(
        self::IMAGE_BOOL               =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_BOOLEAN            =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_SOUNDEX_BOOL       =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_SOUNDEX_BOOLEAN    =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_OTHER_FALSE        =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_OTHER_TRUE         =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_METAPHONE_BOOL     =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_METAPHONE_BOOLEAN  =>  self::PHP_TYPE_BOOLEAN,
        self::IMAGE_REAL               =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_FLOAT              =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_DOUBLE             =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_METAPHONE_REAL     =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_METAPHONE_FLOAT    =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_METAPHONE_DOUBLE   =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_SOUNDEX_DOUBLE     =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_SOUNDEX_FLOAT      =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_SOUNDEX_REAL       =>  self::PHP_TYPE_FLOAT,
        self::IMAGE_INT                =>  self::PHP_TYPE_INTEGER,
        self::IMAGE_INTEGER            =>  self::PHP_TYPE_INTEGER,
        self::IMAGE_METAPHONE_INT      =>  self::PHP_TYPE_INTEGER,
        self::IMAGE_METAPHONE_INTEGER  =>  self::PHP_TYPE_INTEGER,
        self::IMAGE_SOUNDEX_INT        =>  self::PHP_TYPE_INTEGER,
        self::IMAGE_SOUNDEX_INTEGER    =>  self::PHP_TYPE_INTEGER,
        self::IMAGE_STRING             =>  self::PHP_TYPE_STRING,
        self::IMAGE_METAPHONE_STRING   =>  self::PHP_TYPE_STRING,
        self::IMAGE_SOUNDEX_STRING     =>  self::PHP_TYPE_STRING,
    );

    /**
     * Returns <b>true</b> if the given type is internal or part of an
     * extension.
     *
     * @param string $typeName The type name.
     *
     * @return boolean
     */
    public static function isInternalType($typeName)
    {
        self::initTypeToExtension();

        $normalizedName = ltrim($typeName, '\\');
        $normalizedName = strtolower($normalizedName);

        return isset(self::$typeNameToExtension[$normalizedName]);
    }

    /**
     * Returns the package/extension for the given type name. If no package
     * exists, this method will return <b>null</b>.
     *
     * @param string $typeName The type name.
     *
     * @return string
     */
    public static function getTypePackage($typeName)
    {
        self::initTypeToExtension();

        $normalizedName = ltrim($typeName, '\\');
        $normalizedName = strtolower($normalizedName);
        if (isset(self::$typeNameToExtension[$normalizedName])) {
            return self::$typeNameToExtension[$normalizedName];
        }
        return null;
    }

    /**
     * Returns an array with all package/extension names.
     *
     * @return array(string)
     */
    public static function getInternalNamespaces()
    {
        if (self::$internalNamespaces === null) {
            $extensions = array_values(self::initTypeToExtension());
            $extensions = array_unique($extensions);

            self::$internalNamespaces = array_combine($extensions, $extensions);
        }
        return self::$internalNamespaces;
    }

    /**
     * This method will return <b>true</b> when the given package represents a
     * php extension.
     *
     * @param string $packageName Name of a package.
     *
     * @return boolean
     */
    public static function isInternalPackage($packageName)
    {
        $packageNames = self::getInternalNamespaces();
        return isset($packageNames[strtolower($packageName)]);
    }

    /**
     * This method will return <b>true</b> when the given type identifier is in
     * the list of scalar/none-object types.
     *
     * @param string $image The type identifier.
     *
     * @return boolean
     */
    public static function isScalarType($image)
    {
        $image = strtolower($image);
        if (isset(self::$scalarTypes[$image]) === true) {
            return true;
        }
        $image = metaphone($image);
        if (isset(self::$scalarTypes[$image]) === true) {
            return true;
        }
        return isset(self::$scalarTypes[soundex($image)]);
    }

    /**
     * This method will return <b>true</b> when the given type identifier is in
     * the list of primitive types.
     *
     * @param string $image The type image.
     *
     * @return boolean
     * @since  0.9.6
     */
    public static function isPrimitiveType($image)
    {
        return (self::getPrimitiveType($image) !== null);
    }

    /**
     * This method will return a unified type image for a detected source type
     * image.
     *
     * @param string $image The found primitive type image.
     *
     * @return string
     * @since  0.9.6
     */
    public static function getPrimitiveType($image)
    {
        $image = strtolower($image);
        if (isset(self::$primitiveTypes[$image]) === true) {
            return self::$primitiveTypes[$image];
        }
        $image = metaphone($image);
        if (isset(self::$primitiveTypes[$image]) === true) {
            return self::$primitiveTypes[$image];
        }
        $image = soundex($image);
        if (isset(self::$primitiveTypes[$image]) === true) {
            return self::$primitiveTypes[$image];
        }
        return null;
    }

    /**
     * This method will return <b>true</b> when the given image describes a
     * php array type.
     *
     * @param string $image The found type image.
     *
     * @return boolean
     * @since  0.9.6
     */
    public static function isArrayType($image)
    {
        return (strtolower($image) === 'array');
    }

    /**
     * This method reads all available classes and interfaces and checks whether
     * this type belongs to an extension or is internal. All internal and extension
     * classes are collected in an internal data structure.
     *
     * @return array(string=>string)
     */
    private static function initTypeToExtension()
    {
        // Skip when already done.
        if (self::$typeNameToExtension !== null) {
            return self::$typeNameToExtension;
        }

        self::$typeNameToExtension = array('iterator' => '+standard');

        $extensionNames = get_loaded_extensions();
        $extensionNames = array_map('strtolower', $extensionNames);

        foreach ($extensionNames as $extensionName) {
            $extension = new \ReflectionExtension($extensionName);

            $classNames = $extension->getClassNames();
            $classNames = array_map('strtolower', $classNames);

            foreach ($classNames as $className) {
                self::$typeNameToExtension[$className] = '+' . $extensionName;
            }
        }

        return self::$typeNameToExtension;
    }
}
