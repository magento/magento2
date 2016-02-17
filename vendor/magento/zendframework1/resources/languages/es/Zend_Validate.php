<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage Ressource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * EN-Revision: 22075
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given, value should be float, string, or integer" => "El tipo especificado no es válido, el valor debe ser de tipo float, una cadena de texto o entero",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' contiene caracteres que no son alfabéticos ni dígitos",
    "'%value%' is an empty string" => "'%value%' es una cadena de texto vacia",

    // Zend_Validate_Alpha
    "Invalid type given, value should be a string" => "El tipo especificado no es válido, el valor debe ser una cadena de texto",
    "'%value%' contains non alphabetic characters" => "'%value%' contiene caracteres no alfabéticos",
    "'%value%' is an empty string" => "'%value%' es una cadena de texto vacia",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' Fallo la validación de  checksum",
    "'%value%' contains invalid characters" => "'%value%' contiene caracteres no válidos",
    "'%value%' should have a length of %length% characters" => "'%value%' debe tener una longitud de %length% caracteres",
    "Invalid type given, value should be string" => "El tipo especificado no es válido, el valor debe ser una cadena de texto",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' no está incluido entre '%min%' y '%max%'",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' no está exactamente entre '%min%' y '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' no es válido",
    "Failure within the callback, exception returned" => "Falló dentro de la llamada de retorno, ha devuelto una excepción",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' debe contener entre 13 y 19 dígitos",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "El algoritmo de Luhn (checksum del módulo 10) fallo en '%value%'",

    // Zend_Validate_CreditCard
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "El algoritmo de Luhn (checksum del módulo 10) fallo en '%value%'",
    "'%value%' must contain only digits" => "'%value%' debe contener solo dígitos",
    "Invalid type given, value should be a string" => "El tipo especificado no es válido, el valor debe ser una cadena de texto",
    "'%value%' contains an invalid amount of digits" => "'%value%' contiene una cantidad inválida de dígitos",
    "'%value%' is not from an allowed institute" => "'%value%' no es de una institución autorizada",
    "Validation of '%value%' has been failed by the service" => "La validación de '%value%' fallo por causa del servicio",
    "The service returned a failure while validating '%value%'" => "El servicio devolvió un fallo durante la validación de '%value%'",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "El tipo especificado no es válido, el valor debe ser una cadena de texto, entero, array o un objeto Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' no parece ser una fecha válida",
    "'%value%' does not fit the date format '%format%'" => "'%value%' no se ajusta al formato de fecha '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "No fue encontrado ningun registro que coincida con %value%",
    "A record matching '%value%' was found" => "Se encontro un registro coincidente con %value%",

    // Zend_Validate_Digits
    "Invalid type given, value should be string, integer or float" => "El tipo especificado no es válido, el valor debe ser una cadena de texto, entero o float",
    "'%value%' contains characters which are not digits; but only digits are allowed" => "'%value%' contiene caracteres que no son dígitos, solo se permiten dígitos",
    "'%value%' is an empty string" => "'%value%' es una cadena vacía",

    // Zend_Validate_EmailAddress
    "Invalid type given, value should be a string" => "El tipo especificado no es válido, el valor debe ser una cadena de texto",
    "'%value%' is no valid email address in the basic format local-part@hostname" => "'%value%' no es una dirección de correo electrónico válido en el formato local-part@hostname",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' no es un nombre de host válido para la dirección de correo electrónico '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' no parece tener un registro MX válido para la dirección de correo electrónico '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network." => "'%hostname%' no esta en un segmento de red ruteable. La dirección de correo electrónico '%value%' no se debe poder resolver desde una red pública.",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' no es igual al formato dot-atom",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' no es igual al formato quoted-string",
    "'%localPart%' is no valid local part for email address '%value%'" => "'%localPart%' no es una parte local válida para la dirección de correo electrónico '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' excede la longitud permitida",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Demasiados archivos, se permiten un máximo de '%max%' pero se han especificado '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Muy pocos archivos, se esperaba un mí­nimo de '%min%' pero sólo se han especificado '%count%'",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "El CRC32 del archivo '%value%' es incorrecto",
    "A crc32 hash could not be evaluated for the given file" => "No se ha podido calcular el CRC32 del archivo especificado",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "El archivo '%value%' tiene una extensión incorrecta",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "El archivo '%value%' tiene un tipo MIME '%type%' incorrecto",
    "The mimetype of file '%value%' could not be detected" => "No se ha podido determinar el tipo MIME del archivo '%value%'",
    "File '%value%' can not be read" => "El archivo '%value%' no se puede leer",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "El archivo '%value%' no existe",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "El archivo '%value%' tiene una extensión incorrecta",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Todos los archivos deberí­an tener un tamaño máximo de '%max%' pero tiene un tamaño de '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Todos los archivos deberí­an tener un tamaño mí­nimo de '%min%' pero tiene un tamaño de '%size%'",
    "One or more files can not be read" => "Uno o más archivos no se pueden leer",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "El archivo '%value%' no se corresponde con los códigos hash especificados",
    "A hash could not be evaluated for the given file" => "No se ha podido evaluar ningún código hash para el archivo especificado",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "El ancho máxima para la imagen '%value%' deberí­a ser '%maxwidth%' pero es de '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "El ancho mí­nima para la imagen '%value%' deberí­a ser '%minwidth%' pero es de '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "La altura máxima para la imagen '%value%' deberí­a ser '%maxheight%' pero es de '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "La altura mí­nima para la imagen '%value%' deberí­a ser '%minheight%' pero es de '%height%'",
    "The size of image '%value%' could not be detected" => "No se ha podido determinar el tamaño de la imagen '%value%'",
    "File '%value%' can not be read" => "El archivo '%value%' no se puede leer",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "El archivo '%value%' no está comprimido, '%type%' detectado",
    "The mimetype of file '%value%' could not be detected" => "No se ha podido determinar el tipo MIME del archivo '%value%'",
    "File '%value%' can not be read" => "El archivo '%value%' no se puede leer",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "El archivo '%value%' no es una imagen, '%type%' detectado",
    "The mimetype of file '%value%' could not be detected" => "No se ha podido determinar el tipo MIME del archivo '%value%'",
    "File '%value%' can not be read" => "El archivo '%value%' no se puede leer",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "El archivo '%value%' no se corresponde con el MD5 especificado",
    "A md5 hash could not be evaluated for the given file" => "No se ha podido calcular el MD5 del archivo especificado",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "El archivo '%value%' tiene un tipo MIME '%type%' falso",
    "The mimetype of file '%value%' could not be detected" => "No se ha podido determinar el tipo MIME del archivo '%value%'",
    "File '%value%' can not be read" => "El archivo '%value%' no se puede leer",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "El archivo '%value%' existe",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "El archivo '%value%' no es igual al SHA1 especificado",
    "A sha1 hash could not be evaluated for the given file" => "No se ha podido calcular el SHA1 del archivo especificado",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "El tamaño máximo permitido para el archivo '%value%' es '%max%' pero se ha detectado un tamaño de '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "El tamaño mí­nimo permitido para el archivo '%value%' es '%min%' pero se ha detectado un tamaño de '%size%'",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "El tamaño del archivo '%value%' excede el valor definido en el ini",
    "File '%value%' exceeds the defined form size" => "El archivo '%value%' excede el tamaño definido en el formulario",
    "File '%value%' was only partially uploaded" => "El archivo '%value%' ha sido sólo parcialmente subido",
    "File '%value%' was not uploaded" => "El archivo '%value%' no ha sido subido",
    "No temporary directory was found for file '%value%'" => "No se ha encontrado el directorio temporal para el archivo '%value%'",
    "File '%value%' can't be written" => "No se puede escribir en el archivo '%value%'",
    "A PHP extension returned an error while uploading the file '%value%'" => "Una extensión PHP devolvió un error mientras se subí­a el archivo '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "El archivo '%value%' ha sido subido ilegalmente, lo cual podrí­a ser un ataque",
    "File '%value%' was not found" => "Archivo '%value%' no encontrado",
    "Unknown error while uploading file '%value%'" => "error desconocido al intentar subir el archivo '%value%'",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Demasiadas palabras, sólo se permiten '%max%' pero se han contado '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Demasiado pocas palabras, se esperaban al menos '%min%' pero se han contado '%count%'",
    "File '%value%' could not be found" => "No se ha podido encontrar el archivo '%value%'",

    // Zend_Validate_Float
    "Invalid type given, value should be float, string, or integer" => "El tipo especificado no es válido, el valor deberí­a ser de tipo float, una cadena de texto o un entero",
    "'%value%' does not appear to be a float" => "'%value%' no parece ser un float",

// Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' no es mayor que '%min%'",

    // Zend_Validate_Hex
    "Invalid type given, value should be a string" => "El tipo especificado es incorrecto, el valor deberí­a ser una cadena de texto",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' no consta únicamente de dí­gitos y caracteres hexadecimales",

    // Zend_Validate_Hostname
    "Invalid type given, value should be a string" => "El tipo especificado es incorrecto, el valor deberí­a ser una cadena de texto",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' parece una dirección IP, pero éstas no están permitidas",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' parece ser un nombre de dominio DNS pero el TLD no es válido",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' parece ser un nombre de dominio DNS pero contiene una barra en una posición inválida",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' parece ser un nombre de dominio DNS pero su formato no se corresponde con el correcto para el TLD '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' parece ser un nombre de dominio DNS pero no se puede extraer la parte del TLD",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' no se corresponde con la estructura esperada para un nombre de dominio DNS",
    "'%value%' does not appear to be a valid local network name" => "'%value%' no parece ser un nombre de área local válido",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' parece ser un nombre de área local pero no se permiten nombres de área local",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' parece ser un nombre de dominio DNS pero no se puede decodificar la notación de punycode",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Paí­s desconocido dentro del IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' tiene un formato falso de IBAN",
    "'%value%' has failed the IBAN check" => "La prueba de validación de IBAN de '%value%' ha fallado",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Las dos muestras especificados no concuerdan",
    "No token was provided to match against" => "No se ha especificado ninguna muestra a comprobar",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "No se ha encontrado '%value%' en el argumento especificado",

    // Zend_Validate_Int
    "Invalid type given, value should be string or integer" => "El tipo especificado es inválido, el valor deberí­a ser una cadena de texto o un entero",
    "'%value%' does not appear to be an integer" => "'%value%' no parece ser un entero",

    // Zend_Validate_Ip
    "Invalid type given, value should be a string" => "El tipo especificado es incorrecto, el valor deberí­a ser una cadena de texto",
    "'%value%' does not appear to be a valid IP address" => "'%value%' no parece ser una dirección IP válida",

    // Zend_Validate_Isbn
    "Invalid type given, value should be string or integer" => "El tipo especificado es inválido, el valor deberí­a ser una cadena de texto o un entero",
    "'%value%' is not a valid ISBN number" => "El número ISBN especificado ('%value%') no es válido",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' no es menor que '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given, value should be float, string, array, boolean or integer" => "El tipo especificado es inválido, el valor deberí­a ser un float, una cadena de texto, un array, un boolean o un entero",
    "Value is required and can't be empty" => "Se requiere un valor y éste no puede estar vací­o",

    // Zend_Validate_PostCode
    "Invalid type given. The value should be a string or a integer" => "El tipo especificado es incorrecto, el valor deberí­a ser una cadena de texto",
    "'%value%' does not appear to be a postal code" => "'%value%' no parece ser un código postal",

    // Zend_Validate_Regex
    "Invalid type given, value should be string, integer or float" => "El tipo especificado es incorrecto, el valor deberí­a ser de tipo float, una cadena de texto o un entero",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' no concuerda con el patrón '%pattern%' especificado",
    "There was an internal error while using the pattern '%pattern%'" => "Se ha producido un error interno al usar el patrón '%pattern%' especificado",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' no es una especificación válida de frecuencia de cambio",
    "Invalid type given, the value should be a string" => "El tipo especificado es inválido, el valor deberí­a ser una cadena de texto",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' no es un lastmod de mapa web válido",
    "Invalid type given, the value should be a string" => "El tipo especificado es inválido, el valor deberí­a ser una cadena de texto",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' no es una ubicación de mapa web válida",
    "Invalid type given, the value should be a string" => "El tipo especificado es inválido, el valor deberí­a ser una cadena de texto",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' no es una prioridad de mapa web válida",
    "Invalid type given, the value should be a integer, a float or a numeric string" => "El tipo especificado es inválido, el valor deberí­a ser un entero, un float o una cadena de texto numérica",

    // Zend_Validate_StringLength
    "Invalid type given, value should be a string" => "El tipo especificado es incorrecto, el valor deberí­a ser una cadena de texto",
    "'%value%' is less than %min% characters long" => "'%value%' tiene menos de '%min%' caracteres",
    "'%value%' is more than %max% characters long" => "'%value%' tiene más de '%max%' caracteres",
);

