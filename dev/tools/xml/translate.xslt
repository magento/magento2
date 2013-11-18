<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <!-- This is an XSLT 2.0 file.  Any software that supports XSLT 2.0 can be used to convert an xml file to use the
     new translate format.  The software that I used is Saxon9HE, which requires java.
     The jar file can be downloaded from:
            http://saxon.sourceforge.net/
     The converter can be run with the following command:
            java -jar saxon9he.jar -l:on -s:file_to_convert.xml -xsl:translate.xslt -o:converted_file.xml
     -->
    <!--
        Known bugs:
        This script currently may vertically align long lists of attributes which can make it difficult to read diffs.
        This script will merge comments that are the same parent node.
    -->
    <xsl:output indent="yes" />

    <xsl:template name="refactor-translate">
        <xsl:param name="node" />
        <xsl:param name="translate">false</xsl:param>
        <xsl:variable name="to_translate" select="@translate" />
        <xsl:copy>
            <xsl:if test="$translate = 'true'">
                <xsl:attribute name="translate">true</xsl:attribute>
            </xsl:if>
            <xsl:copy-of select="@*[name()!='translate']" />
            <xsl:if test="./comment() != ''">
                <xsl:comment><xsl:value-of select="comment()"/></xsl:comment>
            </xsl:if>
            <xsl:if test="text() != '' ">
                <xsl:value-of select="text()[normalize-space()]" />
            </xsl:if>
            <xsl:for-each select="$node/*">
                <xsl:choose>
                    <xsl:when test="contains($to_translate, local-name())">
                        <xsl:call-template name="refactor-translate">
                            <xsl:with-param name="node" select="." />
                            <xsl:with-param name="translate" select="'true'" />
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:call-template name="refactor-translate">
                            <xsl:with-param name="node" select="." />
                        </xsl:call-template>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="/">
        <xsl:call-template name="refactor-translate">
            <xsl:with-param name="node" select="." />
        </xsl:call-template>
    </xsl:template>
</xsl:stylesheet>