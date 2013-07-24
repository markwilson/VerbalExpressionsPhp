<?php

namespace MarkWilson\VerbalExpression;

use MarkWilson\VerbalExpression;

/**
 * Matcher interface
 *
 * @author Mark Wilson <mark@89allport.co.uk>
 */
interface MatcherInterface
{
    /**
     * Check if the test string matches the expression
     *
     * @param VerbalExpression $verbalExpression Pattern to match
     * @param string           $test             String to test
     *
     * @return boolean
     */
    public function isMatch(VerbalExpression $verbalExpression, $test);

    /**
     * Get all matches for the expression
     *
     * @param VerbalExpression $verbalExpression Pattern to match
     * @param string           $test             String to test
     *
     * @return string[]
     */
    public function getMatches(VerbalExpression $verbalExpression, $test);
}
