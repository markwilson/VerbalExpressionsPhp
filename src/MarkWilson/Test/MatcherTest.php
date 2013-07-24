<?php

namespace MarkWilson\Test;

use MarkWilson\VerbalExpression;
use MarkWilson\VerbalExpression\Matcher;

/**
 * Test matchers
 *
 * @author Mark Wilson <mark@89allport.co.uk>
 */
class MatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test string matching
     *
     * @return void
     */
    public function testStringMatch()
    {
        $verbalExpression = new VerbalExpression();

        $verbalExpression->add('testing');

        $matcher = new Matcher();
        $this->assertTrue($matcher->isMatch($verbalExpression, 'testing'));
    }
}
