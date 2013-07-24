<?php

namespace MarkWilson\Test;

use MarkWilson\VerbalExpression;

/**
 * Class VerbalExpressionOutputTest
 *
 * @package MarkWilson\Test
 * @author  Mark Wilson <mark@rippleffect.com>
 */
class VerbalExpressionOutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test equivalent test from original repo
     *
     * @return void
     */
    public function testEquivalentFromJehna()
    {
        $verbalExpression = new VerbalExpression();

        $verbalExpression->startOfLine()
                         ->then('http')
                         ->maybe('s')
                         ->then('://')
                         ->maybe('www.')
                         ->anythingBut(' ')
                         ->endOfLine();

        $this->assertEquals('^(http)(s)?(\:\/\/)(www\.)?([^\ ]*)$', $verbalExpression->compile());

        $this->assertEquals(1, preg_match($verbalExpression->toString(), 'https://www.google.com'));
    }

    /**
     * Test nested expressions
     *
     * @return void
     */
    public function testNestedExpressions()
    {
        $innerExpression = new VerbalExpression();
        $innerExpression->word();

        $outerExpression = new VerbalExpression();
        $outerExpression->startOfLine()
                        ->find($innerExpression)
                        ->then(' ')
                        ->then($innerExpression)
                        ->endOfLine();

        $this->assertEquals('^(\w+)(\ )(\w+)$', $outerExpression->compile());
    }
}
