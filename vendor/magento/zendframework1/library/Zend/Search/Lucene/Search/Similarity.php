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
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Search_Similarity
{
    /**
     * The Similarity implementation used by default.
     *
     * @var Zend_Search_Lucene_Search_Similarity
     */
    private static $_defaultImpl;

    /**
     * Cache of decoded bytes.
     * Array of floats
     *
     * @var array
     */
    private static $_normTable = array( 0   => 0.0,
                                        1   => 5.820766E-10,
                                        2   => 6.9849193E-10,
                                        3   => 8.1490725E-10,
                                        4   => 9.313226E-10,
                                        5   => 1.1641532E-9,
                                        6   => 1.3969839E-9,
                                        7   => 1.6298145E-9,
                                        8   => 1.8626451E-9,
                                        9   => 2.3283064E-9,
                                        10  => 2.7939677E-9,
                                        11  => 3.259629E-9,
                                        12  => 3.7252903E-9,
                                        13  => 4.656613E-9,
                                        14  => 5.5879354E-9,
                                        15  => 6.519258E-9,
                                        16  => 7.4505806E-9,
                                        17  => 9.313226E-9,
                                        18  => 1.1175871E-8,
                                        19  => 1.3038516E-8,
                                        20  => 1.4901161E-8,
                                        21  => 1.8626451E-8,
                                        22  => 2.2351742E-8,
                                        23  => 2.6077032E-8,
                                        24  => 2.9802322E-8,
                                        25  => 3.7252903E-8,
                                        26  => 4.4703484E-8,
                                        27  => 5.2154064E-8,
                                        28  => 5.9604645E-8,
                                        29  => 7.4505806E-8,
                                        30  => 8.940697E-8,
                                        31  => 1.0430813E-7,
                                        32  => 1.1920929E-7,
                                        33  => 1.4901161E-7,
                                        34  => 1.7881393E-7,
                                        35  => 2.0861626E-7,
                                        36  => 2.3841858E-7,
                                        37  => 2.9802322E-7,
                                        38  => 3.5762787E-7,
                                        39  => 4.172325E-7,
                                        40  => 4.7683716E-7,
                                        41  => 5.9604645E-7,
                                        42  => 7.1525574E-7,
                                        43  => 8.34465E-7,
                                        44  => 9.536743E-7,
                                        45  => 1.1920929E-6,
                                        46  => 1.4305115E-6,
                                        47  => 1.66893E-6,
                                        48  => 1.9073486E-6,
                                        49  => 2.3841858E-6,
                                        50  => 2.861023E-6,
                                        51  => 3.33786E-6,
                                        52  => 3.8146973E-6,
                                        53  => 4.7683716E-6,
                                        54  => 5.722046E-6,
                                        55  => 6.67572E-6,
                                        56  => 7.6293945E-6,
                                        57  => 9.536743E-6,
                                        58  => 1.1444092E-5,
                                        59  => 1.335144E-5,
                                        60  => 1.5258789E-5,
                                        61  => 1.9073486E-5,
                                        62  => 2.2888184E-5,
                                        63  => 2.670288E-5,
                                        64  => 3.0517578E-5,
                                        65  => 3.8146973E-5,
                                        66  => 4.5776367E-5,
                                        67  => 5.340576E-5,
                                        68  => 6.1035156E-5,
                                        69  => 7.6293945E-5,
                                        70  => 9.1552734E-5,
                                        71  => 1.0681152E-4,
                                        72  => 1.2207031E-4,
                                        73  => 1.5258789E-4,
                                        74  => 1.8310547E-4,
                                        75  => 2.1362305E-4,
                                        76  => 2.4414062E-4,
                                        77  => 3.0517578E-4,
                                        78  => 3.6621094E-4,
                                        79  => 4.272461E-4,
                                        80  => 4.8828125E-4,
                                        81  => 6.1035156E-4,
                                        82  => 7.324219E-4,
                                        83  => 8.544922E-4,
                                        84  => 9.765625E-4,
                                        85  => 0.0012207031,
                                        86  => 0.0014648438,
                                        87  => 0.0017089844,
                                        88  => 0.001953125,
                                        89  => 0.0024414062,
                                        90  => 0.0029296875,
                                        91  => 0.0034179688,
                                        92  => 0.00390625,
                                        93  => 0.0048828125,
                                        94  => 0.005859375,
                                        95  => 0.0068359375,
                                        96  => 0.0078125,
                                        97  => 0.009765625,
                                        98  => 0.01171875,
                                        99  => 0.013671875,
                                        100 => 0.015625,
                                        101 => 0.01953125,
                                        102 => 0.0234375,
                                        103 => 0.02734375,
                                        104 => 0.03125,
                                        105 => 0.0390625,
                                        106 => 0.046875,
                                        107 => 0.0546875,
                                        108 => 0.0625,
                                        109 => 0.078125,
                                        110 => 0.09375,
                                        111 => 0.109375,
                                        112 => 0.125,
                                        113 => 0.15625,
                                        114 => 0.1875,
                                        115 => 0.21875,
                                        116 => 0.25,
                                        117 => 0.3125,
                                        118 => 0.375,
                                        119 => 0.4375,
                                        120 => 0.5,
                                        121 => 0.625,
                                        122 => 0.75,
                                        123 => 0.875,
                                        124 => 1.0,
                                        125 => 1.25,
                                        126 => 1.5,
                                        127 => 1.75,
                                        128 => 2.0,
                                        129 => 2.5,
                                        130 => 3.0,
                                        131 => 3.5,
                                        132 => 4.0,
                                        133 => 5.0,
                                        134 => 6.0,
                                        135 => 7.0,
                                        136 => 8.0,
                                        137 => 10.0,
                                        138 => 12.0,
                                        139 => 14.0,
                                        140 => 16.0,
                                        141 => 20.0,
                                        142 => 24.0,
                                        143 => 28.0,
                                        144 => 32.0,
                                        145 => 40.0,
                                        146 => 48.0,
                                        147 => 56.0,
                                        148 => 64.0,
                                        149 => 80.0,
                                        150 => 96.0,
                                        151 => 112.0,
                                        152 => 128.0,
                                        153 => 160.0,
                                        154 => 192.0,
                                        155 => 224.0,
                                        156 => 256.0,
                                        157 => 320.0,
                                        158 => 384.0,
                                        159 => 448.0,
                                        160 => 512.0,
                                        161 => 640.0,
                                        162 => 768.0,
                                        163 => 896.0,
                                        164 => 1024.0,
                                        165 => 1280.0,
                                        166 => 1536.0,
                                        167 => 1792.0,
                                        168 => 2048.0,
                                        169 => 2560.0,
                                        170 => 3072.0,
                                        171 => 3584.0,
                                        172 => 4096.0,
                                        173 => 5120.0,
                                        174 => 6144.0,
                                        175 => 7168.0,
                                        176 => 8192.0,
                                        177 => 10240.0,
                                        178 => 12288.0,
                                        179 => 14336.0,
                                        180 => 16384.0,
                                        181 => 20480.0,
                                        182 => 24576.0,
                                        183 => 28672.0,
                                        184 => 32768.0,
                                        185 => 40960.0,
                                        186 => 49152.0,
                                        187 => 57344.0,
                                        188 => 65536.0,
                                        189 => 81920.0,
                                        190 => 98304.0,
                                        191 => 114688.0,
                                        192 => 131072.0,
                                        193 => 163840.0,
                                        194 => 196608.0,
                                        195 => 229376.0,
                                        196 => 262144.0,
                                        197 => 327680.0,
                                        198 => 393216.0,
                                        199 => 458752.0,
                                        200 => 524288.0,
                                        201 => 655360.0,
                                        202 => 786432.0,
                                        203 => 917504.0,
                                        204 => 1048576.0,
                                        205 => 1310720.0,
                                        206 => 1572864.0,
                                        207 => 1835008.0,
                                        208 => 2097152.0,
                                        209 => 2621440.0,
                                        210 => 3145728.0,
                                        211 => 3670016.0,
                                        212 => 4194304.0,
                                        213 => 5242880.0,
                                        214 => 6291456.0,
                                        215 => 7340032.0,
                                        216 => 8388608.0,
                                        217 => 1.048576E7,
                                        218 => 1.2582912E7,
                                        219 => 1.4680064E7,
                                        220 => 1.6777216E7,
                                        221 => 2.097152E7,
                                        222 => 2.5165824E7,
                                        223 => 2.9360128E7,
                                        224 => 3.3554432E7,
                                        225 => 4.194304E7,
                                        226 => 5.0331648E7,
                                        227 => 5.8720256E7,
                                        228 => 6.7108864E7,
                                        229 => 8.388608E7,
                                        230 => 1.00663296E8,
                                        231 => 1.17440512E8,
                                        232 => 1.34217728E8,
                                        233 => 1.6777216E8,
                                        234 => 2.01326592E8,
                                        235 => 2.34881024E8,
                                        236 => 2.68435456E8,
                                        237 => 3.3554432E8,
                                        238 => 4.02653184E8,
                                        239 => 4.69762048E8,
                                        240 => 5.3687091E8,
                                        241 => 6.7108864E8,
                                        242 => 8.0530637E8,
                                        243 => 9.395241E8,
                                        244 => 1.07374182E9,
                                        245 => 1.34217728E9,
                                        246 => 1.61061274E9,
                                        247 => 1.87904819E9,
                                        248 => 2.14748365E9,
                                        249 => 2.68435456E9,
                                        250 => 3.22122547E9,
                                        251 => 3.75809638E9,
                                        252 => 4.2949673E9,
                                        253 => 5.3687091E9,
                                        254 => 6.4424509E9,
                                        255 => 7.5161928E9 );


    /**
     * Set the default Similarity implementation used by indexing and search
     * code.
     *
     * @param Zend_Search_Lucene_Search_Similarity $similarity
     */
    public static function setDefault(Zend_Search_Lucene_Search_Similarity $similarity)
    {
        self::$_defaultImpl = $similarity;
    }


    /**
     * Return the default Similarity implementation used by indexing and search
     * code.
     *
     * @return Zend_Search_Lucene_Search_Similarity
     */
    public static function getDefault()
    {
        if (!self::$_defaultImpl instanceof Zend_Search_Lucene_Search_Similarity) {
            #require_once 'Zend/Search/Lucene/Search/Similarity/Default.php';
            self::$_defaultImpl = new Zend_Search_Lucene_Search_Similarity_Default();
        }

        return self::$_defaultImpl;
    }


    /**
     * Computes the normalization value for a field given the total number of
     * terms contained in a field.  These values, together with field boosts, are
     * stored in an index and multipled into scores for hits on each field by the
     * search code.
     *
     * Matches in longer fields are less precise, so implemenations of this
     * method usually return smaller values when 'numTokens' is large,
     * and larger values when 'numTokens' is small.
     *
     * That these values are computed under
     * IndexWriter::addDocument(Document) and stored then using
     * encodeNorm(float).  Thus they have limited precision, and documents
     * must be re-indexed if this method is altered.
     *
     * fieldName - name of field
     * numTokens - the total number of tokens contained in fields named
     *             'fieldName' of 'doc'.
     * Returns a normalization factor for hits on this field of this document
     *
     * @param string $fieldName
     * @param integer $numTokens
     * @return float
     */
    abstract public function lengthNorm($fieldName, $numTokens);

    /**
     * Computes the normalization value for a query given the sum of the squared
     * weights of each of the query terms.  This value is then multipled into the
     * weight of each query term.
     *
     * This does not affect ranking, but rather just attempts to make scores
     * from different queries comparable.
     *
     * sumOfSquaredWeights - the sum of the squares of query term weights
     * Returns a normalization factor for query weights
     *
     * @param float $sumOfSquaredWeights
     * @return float
     */
    abstract public function queryNorm($sumOfSquaredWeights);


    /**
     *  Decodes a normalization factor stored in an index.
     *
     * @param integer $byte
     * @return float
     */
    public static function decodeNorm($byte)
    {
        return self::$_normTable[$byte & 0xFF];
    }


    /**
     * Encodes a normalization factor for storage in an index.
     *
     * The encoding uses a five-bit exponent and three-bit mantissa, thus
     * representing values from around 7x10^9 to 2x10^-9 with about one
     * significant decimal digit of accuracy.  Zero is also represented.
     * Negative numbers are rounded up to zero.  Values too large to represent
     * are rounded down to the largest representable value.  Positive values too
     * small to represent are rounded up to the smallest positive representable
     * value.
     *
     * @param float $f
     * @return integer
     */
    static function encodeNorm($f)
    {
      return self::_floatToByte($f);
    }

    /**
     * Float to byte conversion
     *
     * @param integer $b
     * @return float
     */
    private static function _floatToByte($f)
    {
        // round negatives up to zero
        if ($f <= 0.0) {
            return 0;
        }

        // search for appropriate value
        $lowIndex = 0;
        $highIndex = 255;
        while ($highIndex >= $lowIndex) {
            // $mid = ($highIndex - $lowIndex)/2;
            $mid = ($highIndex + $lowIndex) >> 1;
            $delta = $f - self::$_normTable[$mid];

            if ($delta < 0) {
                $highIndex = $mid-1;
            } elseif ($delta > 0) {
                $lowIndex  = $mid+1;
            } else {
                return $mid; // We got it!
            }
        }

        // round to closest value
        if ($highIndex != 255 &&
            $f - self::$_normTable[$highIndex] > self::$_normTable[$highIndex+1] - $f ) {
            return $highIndex + 1;
        } else {
            return $highIndex;
        }
    }


    /**
     * Computes a score factor based on a term or phrase's frequency in a
     * document.  This value is multiplied by the idf(Term, Searcher)
     * factor for each term in the query and these products are then summed to
     * form the initial score for a document.
     *
     * Terms and phrases repeated in a document indicate the topic of the
     * document, so implementations of this method usually return larger values
     * when 'freq' is large, and smaller values when 'freq'
     * is small.
     *
     * freq - the frequency of a term within a document
     * Returns a score factor based on a term's within-document frequency
     *
     * @param float $freq
     * @return float
     */
    abstract public function tf($freq);

    /**
     * Computes the amount of a sloppy phrase match, based on an edit distance.
     * This value is summed for each sloppy phrase match in a document to form
     * the frequency that is passed to tf(float).
     *
     * A phrase match with a small edit distance to a document passage more
     * closely matches the document, so implementations of this method usually
     * return larger values when the edit distance is small and smaller values
     * when it is large.
     *
     * distance - the edit distance of this sloppy phrase match
     * Returns the frequency increment for this match
     *
     * @param integer $distance
     * @return float
     */
    abstract public function sloppyFreq($distance);


    /**
     * Computes a score factor for a simple term or a phrase.
     *
     * The default implementation is:
     *   return idfFreq(searcher.docFreq(term), searcher.maxDoc());
     *
     * input - the term in question or array of terms
     * reader - reader the document collection being searched
     * Returns a score factor for the term
     *
     * @param mixed $input
     * @param Zend_Search_Lucene_Interface $reader
     * @return a score factor for the term
     */
    public function idf($input, Zend_Search_Lucene_Interface $reader)
    {
        if (!is_array($input)) {
            return $this->idfFreq($reader->docFreq($input), $reader->count());
        } else {
            $idf = 0.0;
            foreach ($input as $term) {
                $idf += $this->idfFreq($reader->docFreq($term), $reader->count());
            }
            return $idf;
        }
    }

    /**
     * Computes a score factor based on a term's document frequency (the number
     * of documents which contain the term).  This value is multiplied by the
     * tf(int) factor for each term in the query and these products are
     * then summed to form the initial score for a document.
     *
     * Terms that occur in fewer documents are better indicators of topic, so
     * implemenations of this method usually return larger values for rare terms,
     * and smaller values for common terms.
     *
     * docFreq - the number of documents which contain the term
     * numDocs - the total number of documents in the collection
     * Returns a score factor based on the term's document frequency
     *
     * @param integer $docFreq
     * @param integer $numDocs
     * @return float
     */
    abstract public function idfFreq($docFreq, $numDocs);

    /**
     * Computes a score factor based on the fraction of all query terms that a
     * document contains.  This value is multiplied into scores.
     *
     * The presence of a large portion of the query terms indicates a better
     * match with the query, so implemenations of this method usually return
     * larger values when the ratio between these parameters is large and smaller
     * values when the ratio between them is small.
     *
     * overlap - the number of query terms matched in the document
     * maxOverlap - the total number of terms in the query
     * Returns a score factor based on term overlap with the query
     *
     * @param integer $overlap
     * @param integer $maxOverlap
     * @return float
     */
    abstract public function coord($overlap, $maxOverlap);
}

