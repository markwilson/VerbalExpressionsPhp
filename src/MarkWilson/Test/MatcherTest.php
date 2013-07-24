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

        $verbalExpression->find('testing');

        $matcher = new Matcher();
        $this->assertTrue($matcher->isMatch($verbalExpression, 'testing'));
    }

    /**
     * Test matches array
     *
     * @return void
     */
    public function testMatchesArray()
    {
        $verbalExpression = new VerbalExpression();

        $verbalExpression->find('testing');

        $matcher = new Matcher();

        // as the default is to compile the expression into (testing) we will get an array of matches and submatches
        $this->assertEquals(
            array(
                'testing',
                'testing'
            ),
            $matcher->getMatches($verbalExpression, 'testing')
        );
    }

    /**
     * Test matches array when not capturing sub patterns
     *
     * @return void
     */
    public function testMatchesWithoutSubPatternCapture()
    {
        $verbalExpression = new VerbalExpression();

        $verbalExpression->disableSubPatternCapture()->find('testing');

        $matcher = new Matcher();

        $this->assertEquals(
            array(
                'testing'
            ),
            $matcher->getMatches($verbalExpression, 'testing')
        );
    }
}
