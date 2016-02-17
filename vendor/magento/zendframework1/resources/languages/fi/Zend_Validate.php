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
    "Invalid type given. String, integer or float expected" => "Epäkelpo syöte. Pitäisi olla liukuluku, merkkijono tai kokonaisluku",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' on virheelinen, ainoastaan aakkoset ja numerot ovat sallittuja",
    "'%value%' is an empty string" => "'%value%' on tyhjä merkkijono",

    // Zend_Validate_Alpha
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' contains non alphabetic characters" => "'%value%' on virheellinen, ainoastaan aakkoset ovat sallittuja",
    "'%value%' is an empty string" => "'%value%' on tyhjä merkkijono",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "Syötteen '%value%' tarkistusluvun vahvistus epäonnistui",
    "'%value%' contains invalid characters" => "'%value%' sisältää epäkelpoja merkkejä",
    "'%value%' should have a length of %length% characters" => "'%value%' pitäisi olla %length% merkkiä pitkä",
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' ei ole luku väliltä %min%-%max%",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' ei ole luku väliltä %min%-%max%, poislukien ylä- ja alarajat",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' on epäkelpo",
    "An exception has been raised within the callback" => "Odottamaton virhe, callback-validaattori palautti poikkeuksen",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' pitää olla luku väliltä 13-19",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhn-algoritmin (mod 10) suoritus syötteelle '%value%' epäonnistui",

    // Zend_Validate_CreditCard
    "'%value%' seems to contain an invalid checksum" => "Syötteen '%value%' tarkistusluku on viallinen",
    "'%value%' must contain only digits" => "'%value%' saa sisältää ainoastaan numeroita",
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' contains an invalid amount of digits" => "'%value%' sisältää väärän määrän numeroita",
    "'%value%' is not from an allowed institute" => "'%value%' ei ole sallitun luottolaitoksen alkuosa",
    "'%value%' seems to be an invalid creditcard number" => "Luottokortin numero '%value%' tulkittiin virheelliseksi",
    "An exception has been raised while validating '%value%'" => "Kortin '%value%' varmennus epäonnistui, palvelu palautti virheen",

    // Zend_Validate_Date
    "Invalid type given. String, integer, array or Zend_Date expected" => "Epäkelpo syöte. Pitäisi olla merkkijono, kokonaisluku, taulukko tai Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' ei ole kelvollinen päivä",
    "'%value%' does not fit the date format '%format%'" => "'%value%' ei ole muotoa '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Rekisteristä ei löytynyt arvoa, joka vastaisi syötettä '%value%'",
    "A record matching '%value%' was found" => "Rekisteristä löytyi syötettä '%value%' vastaava arvo",

    // Zend_Validate_Digits
    "Invalid type given. String, integer or float expected" => "Epäkelpo syöte. Pitäisi olla merkkijono, kokonaisluku tai liukuluku",
    "'%value%' must contain only digits" => "'%value%' on virheellinen, ainoastaan numerot ovat sallittuja",
    "'%value%' is an empty string" => "'%value%' on tyhjä merkkijono",

    // Zend_Validate_EmailAddress
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' on virheellinen sähköpostiosoite, ei vastaa muotoa paikallisosa@alue",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' on virheellinen verkkotunnus osoitteelle '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "Osoitteen '%value%' verkkotunnukselle '%hostname%' ei löydy MX-tietuetta",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network" => "'%hostname%' ei ole reititettävän verkon osa. Sähköpostiosoitetta '%value%' ei pitäisi selvittää julkisesta verkosta.",
    "'%localPart%' can not be matched against dot-atom format" => "Virheellinen paikallisosa, '%localPart%' ei ole verrattavissa dot-atom -muotoon",
    "'%localPart%' can not be matched against quoted-string format" => "Virheellinen paikallisosa, '%localPart%' ei ole verrattavissa quoted-string -muotoon",
    "'%localPart%' is not a valid local part for email address '%value%'" => "Sähköpostiosoitteen '%value%' paikallisosa '%localPart%' on virheellinen",
    "'%value%' exceeds the allowed length" => "Osoite '%value%' on liian pitkä",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Virheellinen määrä tiedostoja, maksimimäärä on '%max%', vastaanotettiin '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Virheellinen määrä tiedostoja, minimimäärä on '%min%', vastaanotettiin '%count%'",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Tiedoston '%value%' crc32-tarkistusluku ei vastaa annettua",
    "A crc32 hash could not be evaluated for the given file" => "Tarkistuslukua crc32 ei pystytty määrittämään vastaanotetulle tiedostolle",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Tiedostolla '%value%' on virheellinen pääte",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Tiedoston '%value%' MIME-tyyppi '%type%' on virheellinen",
    "The mimetype of file '%value%' could not be detected" => "Tiedoston '%value%' MIME-tyyppiä ei pystytty todentamaan",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Tiedostoa '%value%' ei ole olemassa",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Tiedostolla '%value%' on virheellinen pääte",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Kaikkien tiedostojen yhteenlaskettu koko saa olla maksimissaan '%max%', vastaanotettiin '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Kaikkien tiedostojen yhteenlaskettu koko pitää olla vähintään '%min%', vastaanotettiin '%size%'",
    "One or more files can not be read" => "Yhtä tai useampaa tiedostoa ei voida lukea",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Tiedoston '%value%' tarkastusluku ei vastaa annettua",
    "A hash could not be evaluated for the given file" => "Tarkistuslukua ei pystytty määrittämään vastaanotetulle tiedostolle",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Kuvan '%value%' maksimileveys on '%maxwidth%', annettu '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Kuvan '%value%' minimileveys on '%minwidth%', annettu '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Kuvan '%value%' maksimikorkeus on '%maxheight%', annettu '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Kuvan '%value%' minimikorkeus on '%minheight%', annettu '%height%'",
    "The size of image '%value%' could not be detected" => "Kuvan '%value%' kokoa ei voida todentaa",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Tiedosto '%value%' ei ole pakattu, vastaanotettiin tyyppiä '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Tiedoston '%value%' MIME-tyyppiä ei pystytty todentamaan",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Tiedosto '%value%' ei ole kuvatiedosto, vastaanotettiin tyyppiä '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Tiedoston '%value%' MIME-tyyppiä ei pystytty todentamaan",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Tiedoston '%value%' tarkistusluku ei vastaa annettua (md5)",
    "A md5 hash could not be evaluated for the given file" => "Tiedostolle ei voitu määrittää md5-tarkistuslukua",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Tiedoston '%value%' MIME-tyyppi '%type%' on virheellinen",
    "The mimetype of file '%value%' could not be detected" => "Tiedoston '%value%' MIME-tyyppiä ei pystytty todentamaan",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Tiedostoa '%value%' ei ole olemassa",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Tiedoston '%value%' tarkistusluku ei vastaa annettua (sha1)",
    "A sha1 hash could not be evaluated for the given file" => "Tiedostolle ei voitu määrittää sha1-tarkistuslukua",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Tiedoston '%value%' maksimikoko on '%max%', vastaanotettu '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Tiedoston '%value%' minimikoko on '%min%', vastaanotettu '%size%'",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voidea lukea tai sitä ei ole",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Tiedosto '%value%' ylittää ini-tiedostossa määritellyn tiedostokoon",
    "File '%value%' exceeds the defined form size" => "Tiedosto '%value%' ylittää lomakkeessa määritellyn tiedostokoon",
    "File '%value%' was only partially uploaded" => "Tiedosto '%value%' vastaanotettiin ainoastaan osittain",
    "File '%value%' was not uploaded" => "Tiedostoa '%value%' ei lähetetty",
    "No temporary directory was found for file '%value%'" => "Väliaikaishakemistoa ei löytynyt tiedostolle '%value%'",
    "File '%value%' can't be written" => "Tiedostoon '%value%' ei voida kirjoittaa",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP:n lisäosa palautti virheen kesken tiedoston '%value%' lähetyksen",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Tiedoston '%value%' lähetyksessä haivattu laittomuus, mahdollinen hyökkäys",
    "File '%value%' was not found" => "Tiedostoa '%value%' ei löydy",
    "Unknown error while uploading file '%value%'" => "Tiedoston '%value%' lähetyksessä tapahtui tunnistamaton virhe",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Virheellinen määrä sanoja, maksimäärä on '%max%', annettu '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Virheellinen määrä sanoja, minimimäärä on '%min%', annettu '%count%'",
    "File '%value%' is not readable or does not exist" => "Tiedostoa '%value%' ei voida lukea tai sitä ei ole",

    // Zend_Validate_Float
    "Invalid type given. String, integer or float expected" => "Epäkelpo syöte. Pitäisi olla liukuluku, merkkijono tai kokonaisluku",
    "'%value%' does not appear to be a float" => "'%value%' ei ole liukuluku",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' ei ole suurempi kuin '%min%'",

    // Zend_Validate_Hex
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' voi sisältää ainoastaan heksadeslimaalin muotoisia merkkejä",

    // Zend_Validate_Hostname
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' näyttäisi olevan ip-osoite eikä verkkotunnus",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' verkkotunnuksen TLD-osa ei ole tunnettu",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' näyttäisi olevan käypä verkkotunnus, mutta sisältää viivan väärässä paikassa",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' näyttäisi olevan käypä verkkotunnus, mutta sen TLD-osa '%tld%' on virheellinen",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' verkkotunnuksen TLD-osaa ei pystytty erottamaan",
    "'%value%' does not match the expected structure for a DNS hostname" => "Verkkotunnus '%value%' on jäsennykseltään virheellinen",
    "'%value%' does not appear to be a valid local network name" => "'%value%' on epäkelpo paikallisverkkon tunnus",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' tulkittiin paikallisverkon tunnukseksi, jotka eivät ole sallittuja",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "Verkkotunnuksen '%value%' punycode-koodauksen purku epäonnistui",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Maata ei pystytty tunnistamaan IBAN-koodista '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' on väärän muotoinen IBAN-koodi",
    "'%value%' has failed the IBAN check" => "'%value%' IBAN-koodin tarkastus epäonnistui",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Annetut kaksi arvoa eivät täsmää",
    "No token was provided to match against" => "Toinen arvoista puuttuu",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' ei löydy sallittujen syötteiden joukosta",

    // Zend_Validate_Int
    "Invalid type given. String or integer expected" => "Epäkelpo syöte. Pitäisi olla merkkijono tai kokonaisluku",
    "'%value%' does not appear to be an integer" => "'%value%' ei ole kokonaisluku",

    // Zend_Validate_Ip
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' does not appear to be a valid IP address" => "'%value%' ei ole käypä IP-osoite",

    // Zend_Validate_Isbn
    "Invalid type given. String or integer expected" => "Epäkelpo syöte. Pitäisi olla merkkijono tai kokonaisluku",
    "'%value%' is not a valid ISBN number" => "'%value%' ei ole käypä ISBN-numero",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' ei ole pienempi kuin '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given. String, integer, float, boolean or array expected" => "Epäkelpo syöte. Pitäisi olla kokonaisluku, liukuluku, boolean tai taulukko",
    "Value is required and can't be empty" => "Kenttä ei voi olla tyhjä",

    // Zend_Validate_PostCode
    "Invalid type given. String or integer expected" => "Epäkelpo syöte. Pitäisi olla merkkijono tai kokonaisluku",
    "'%value%' does not appear to be a postal code" => "'%value%' ei ole käypä postiosoite",

    // Zend_Validate_Regex
    "Invalid type given. String, integer or float expected" => "Epäkelpo syöte. Pitäisi olla merkkijono, kokonaisluku tai liukuluku",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' ei ole muotoa '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Sisäinen virhe käytettäessa muotoa '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' ei ole käypä sivukartan muutosnopeus",
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' ei ole käypä arvo sivukartan viimeksimuokatuksi arvoksi",
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' ei ole käypä sivukartan sijainti",
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' ei ole käypä sivukartan prioriteetti",
    "Invalid type given. Numeric string, integer or float expected" => "Epäkelpo syöte. Pitäisi olla kokonaisluku tai liukuluku",

    // Zend_Validate_StringLength
    "Invalid type given. String expected" => "Epäkelpo syöte. Pitäisi olla merkkijono",
    "'%value%' is less than %min% characters long" => "'%value%' on lyhyempi kuin vaaditut %min% merkkiä",
    "'%value%' is more than %max% characters long" => "'%value%' on pidempi kuin sallitut %max% merkkiä",
);
