<?php

namespace MarkWilson\Test;

require_once __DIR__ . '/../VerbalExpression.php';

use MarkWilson\VerbalExpression;

/**
 * Class VerbalExpressionOutputTest
 *
 * @package MarkWilson\Test
 * @author  Mark Wilson <mark@rippleffect.com>
 */
class VerbalExpressionOutputTest extends \PHPUnit_Framework_TestCase
{
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

        $this->assertEquals($verbalExpression->compile(), '^(http)(s)?(\:\/\/)(www\.)?([^\ ]*)$');

        $this->assertEquals(1, preg_match($verbalExpression->toString(), 'https://www.google.com'));
    }
}