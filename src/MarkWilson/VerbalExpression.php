<?php

namespace MarkWilson;

/**
 * Verbal Expression regular expression builder
 *
 * Based on JS version - jehna/VerbalExpressions
 * - https://github.com/jehna/VerbalExpressions
 *
 * @author Mark Wilson <mark@89allport.co.uk>
 */
class VerbalExpression
{
    /**
     * String of prefixes
     *
     * @var string
     */
    private $prefixes = '';
    /**
     * String of suffixes
     *
     * @var string
     */
    private $suffixes = '';
    /**
     * Current expression string
     *
     * @var string
     */
    private $expression = '';
    /**
     * String of modifiers
     *
     * @var string
     */
    private $modifiers = '';
    /**
     * Use sub patterns
     *
     * @var boolean
     */
    private $subPattern = true;

    /**
     * Match anything
     *
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function anything($subPattern = null)
    {
        return $this->add('.*', $subPattern);
    }

    /**
     * Match anything except characters in $value
     *
     * @param string  $value      String of characters to ignore
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function anythingBut($value, $subPattern = null)
    {
        return $this->add('[^' . $this->sanitise($value) . ']*', $subPattern);
    }

    /**
     * Add end of line suffix
     *
     * @return $this
     */
    public function endOfLine()
    {
        if (false === strpos($this->suffixes, '$')) {
            $this->suffixes .= '$';
        }

        return $this;
    }

    /**
     * Shorthand for then method
     *
     * @param string  $value String to find
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function find($value, $subPattern = null)
    {
        return $this->then($value, $subPattern);
    }

    /**
     * Add value or not
     *
     * @param string  $value Maybe value
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function maybe($value, $subPattern = null)
    {
        return $this->add($this->sanitise($value), $subPattern, '?');
    }

    /**
     * Add start of line prefix
     *
     * @return $this
     */
    public function startOfLine()
    {
        if (false === strpos($this->prefixes, '^')) {
            $this->prefixes = '^' . $this->prefixes;
        }

        return $this;
    }

    /**
     * Add a sanitised value check
     *
     * @param string  $value Value to sanitise
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function then($value, $subPattern = null)
    {
        return $this->add($this->sanitise($value), $subPattern);
    }

    /**
     * Shorthand for anyOf method
     *
     * @param string  $value String of characters
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function any($value, $subPattern = null)
    {
        return $this->anyOf($value, $subPattern);
    }

    /**
     * Any of the specified characters
     *
     * @param string  $value String of characters
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function anyOf($value, $subPattern = null)
    {
        return $this->add('[' . $this->sanitise($value) . ']', $subPattern);
    }

    /**
     * Shorthand for lineBreak method
     *
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function br($subPattern = null)
    {
        return $this->lineBreak($subPattern);
    }

    /**
     * Search for new line
     *
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function lineBreak($subPattern = null)
    {
        return $this->add("\\n|(?:\\r\\n)", $subPattern);
    }

    /**
     * Characters in range - requires pairs of arguments, provide subPattern as final argument
     *
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function range($subPattern = null)
    {
        $arguments = func_get_args();

        // odd number of arguments, must assume last is subPattern
        if (count($arguments) % 2 === 1) {
            $subPattern = array_pop($arguments);
        } else {
            $subPattern = null;
        }

        $value     = '[';

        for ($fromIndex = 0; $fromIndex < count($arguments); $fromIndex += 2) {
            $toIndex = $fromIndex + 1;
            if ($toIndex >= count($arguments)) {
                break;
            }

            $from = $this->sanitise($arguments[$fromIndex]);
            $to   = $this->sanitise($arguments[$toIndex]);

            $value .= $from . '-' . $to;
        }

        $value .= ']';

        return $this->add($value, $subPattern);
    }

    /**
     * Add tab character
     *
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function tab($subPattern = null)
    {
        return $this->add("\\t", $subPattern);
    }

    /**
     * Match entire word
     *
     * @param boolean $subPattern Collect as a sub pattern
     *
     * @return $this
     */
    public function word($subPattern = null)
    {
        return $this->add("\\w+", $subPattern);
    }

    /**
     * Remove case sensitivity
     *
     * @param boolean $enable Remove case sensitivity?
     *
     * @return $this
     */
    public function withAnyCase($enable = true)
    {
        if ($enable) {
            $this->addModifier('i');
        } else {
            $this->removeModifier('i');
        }

        return $this;
    }

    /**
     * Search on a single line
     *
     * @param boolean $enable Search on a single line?
     *
     * @return $this
     */
    public function searchOneLine($enable = true)
    {
        if ($enable) {
            $this->removeModifier('m');
        } else {
            $this->addModifier('m');
        }

        return $this;
    }

    /**
     * Add a regular expression partial
     *
     * @param string  $expression           Regular expression partial to add
     * @param boolean $subPattern           Collect as a sub pattern
     * @param string  $additionalCharacters Additional characters after brackets - e.g. ?
     *
     * @return $this
     */
    public function add($expression, $subPattern = null, $additionalCharacters = '')
    {
        $this->expression .= $this->addBrackets($expression, $subPattern) . $additionalCharacters;

        return $this;
    }

    /**
     * Shortcut for adding multiples
     *
     * @param string $value Value to be multiples
     *
     * @return $this
     */
    public function multiple($value)
    {
        $value = $this->sanitise($value);

        switch (substr($value, -1)) {
            case '*':
            case '+':
                break;
            default:
                $value .= '+';
        }

        return $this->add($value);
    }

    /**
     * Add or expression
     *
     * @param string $value Or expression
     *
     * @return $this
     */
    public function orPipe($value)
    {
        if (false === strpos($this->prefixes, '(')) {
            $this->prefixes .= $this->generateOpeningBracket();
        }

        if (false === strpos($this->suffixes, ')')) {
            $this->suffixes = ')' . $this->suffixes;
        }

        $this->add(')|' . $this->generateOpeningBracket());

        return $this->then($value);
    }

    /**
     * Convert to a string
     *
     * @return string
     */
    public function __toString()
    {
        return '/' . $this->compile() . '/';
    }

    /**
     * Shorthand for __toString method
     *
     * @return string
     */
    public function toString()
    {
        return (string)$this;
    }

    /**
     * Compile all but the start and end characters
     *
     * @return string
     */
    public function compile()
    {
        return $this->prefixes . $this->expression . $this->suffixes;
    }

    /**
     * Sanitise the added value
     *
     * @param mixed $value Value to sanitise
     *
     * @return string
     */
    private function sanitise($value)
    {
        if ($value instanceof VerbalExpression) {
            // no need to run sanitisation on an existing expression object
            return $value;
        }

        if (!is_string($value)) {
            $value = (string)$value;
        }

        $regExp  = '/[^\w]/';

        return preg_replace_callback(
            $regExp,
            function ($matches) {
                return "\\" . $matches[0];
            },
            $value
        );
    }

    /**
     * Add a modifier
     *
     * @param string $modifier Modifier character
     *
     * @return $this
     */
    private function addModifier($modifier)
    {
        if ($this->hasModifier($modifier)) {
            $this->modifiers .= $modifier;
        }

        return $this;
    }

    /**
     * Remove modifier
     *
     * @param string $modifier Modifier character
     *
     * @return $this
     */
    private function removeModifier($modifier)
    {
        if ($this->hasModifier($modifier)) {
            $this->modifiers = str_replace($modifier, '', $this->modifiers);
        }

        return $this;
    }

    /**
     * Check if modifier is set
     *
     * @param string $modifier Modifier character
     *
     * @return boolean
     */
    private function hasModifier($modifier)
    {
        return strpos($this->modifiers, $modifier) !== false;
    }

    /**
     * Wrap string in brackets
     *
     * @param string|VerbalExpression $text       Text to wrap
     * @param boolean                 $subPattern Use sub patterns
     *
     * @return string
     */
    private function addBrackets($text, $subPattern = null)
    {
        if ($text instanceof VerbalExpression) {
            return $text->compile();
        }

        return $this->generateOpeningBracket($subPattern) .  $text . ')';
    }

    /**
     * Generate the opening bracket
     *
     * @param boolean $subPattern Use sub patterns?
     *
     * @return string
     */
    private function generateOpeningBracket($subPattern = null)
    {
        if ($subPattern !== true && (false === $subPattern || false === $this->subPattern)) {
            return '(?:';
        }

        return '(';
    }
}
