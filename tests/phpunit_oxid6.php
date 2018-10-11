<?php

/**
 * Helper class to access StringMatchIgnoreWhitespace class for both OXID versions without changing of unit test files
 * Needed because on OXID 6 we have namespaces for etsy plugin on newer versions
 *
 * @author Benjamin Gutmann <benjamin.gutmann@bestit-online.de>
 */
class MatchIgnoreWhitespace extends PHPUnit\Extensions\Constraint\StringMatchIgnoreWhitespace
{

}
