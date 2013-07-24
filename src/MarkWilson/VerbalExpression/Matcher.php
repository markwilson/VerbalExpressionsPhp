<?php

namespace MarkWilson\VerbalExpression;

use MarkWilson\VerbalExpression;

/**
 * Base pattern matcher
 *
 * @author Mark Wilson <mark@89allport.co.uk>
 */
class Matcher implements MatcherInterface
{
    /**
     * Check if the test string matches the expression
     *
     * @param VerbalExpression $verbalExpression Pattern to match
     * @param string           $test             String to test
     *
     * @return boolean
     */
    public function isMatch(VerbalExpression $verbalExpression, $test)
    {
        return preg_match($verbalExpression, $test) > 0;
    }
}
