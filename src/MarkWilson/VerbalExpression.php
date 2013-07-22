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
     * Match anything
     *
     * @return $this
     */
    public function anything()
    {
        return $this->add('(.*)');
    }

    /**
     * Match anything except characters in $value
     *
     * @param string $value String of characters to ignore
     *
     * @return $this
     */
    public function anythingBut($value)
    {
        return $this->add('([^' . $this->sanitise($value) . ']*)');
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
     * @param string $value String to find
     *
     * @return $this
     */
    public function find($value)
    {
        return $this->then($value);
    }

    /**
     * Add value or not
     *
     * @param string $value Maybe value
     *
     * @return $this
     */
    public function maybe($value)
    {
        return $this->add('(' . $this->sanitise($value) . ')?');
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
     * @param string $value Value to sanitise
     *
     * @return $this
     */
    public function then($value)
    {
        return $this->add('(' . $this->sanitise($value) . ')');
    }

    /**
     * Shorthand for anyOf method
     *
     * @param string $value String of characters
     *
     * @return $this
     */
    public function any($value)
    {
        return $this->anyOf($value);
    }

    /**
     * Any of the specified characters
     *
     * @param string $value String of characters
     *
     * @return $this
     */
    public function anyOf($value)
    {
        return $this->add('[' . $this->sanitise($value) . ']');
    }

    /**
     * Shorthand for lineBreak method
     *
     * @return $this
     */
    public function br()
    {
        return $this->lineBreak();
    }

    /**
     * Search for new line
     *
     * @return $this
     */
    public function lineBreak()
    {
        return $this->add("(\\n|(\\r\\n))");
    }

    /**
     * Characters in range
     *
     * @return $this
     */
    public function range()
    {
        $arguments = func_get_args();
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

        return $this->add($value);
    }

    /**
     * Add tab character
     *
     * @return $this
     */
    public function tab()
    {
        return $this->add("\\t");
    }

    /**
     * Match entire word
     *
     * @return $this
     */
    public function word()
    {
        return $this->add("\\w+");
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
     * @param string $expression Regular expression partial to add
     *
     * @return $this
     */
    public function add($expression)
    {
        $this->expression .= $expression;

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
            $this->prefixes .= '(';
        }

        if (false === strpos($this->suffixes, ')')) {
            $this->suffixes = ')' . $this->suffixes;
        }

        $this->add(')|(');
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
            $value = $value->compile();
        } elseif (!is_string($value)) {
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
}
