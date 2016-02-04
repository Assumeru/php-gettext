<?php
namespace gettext\pluralparser\tokens;

class NegateToken extends Token {
	/**
	 * @var int|Token The token to negate
	 */
	private $value;

	public function __construct() {
		parent::__construct(static::PREC_UNARY);
	}

	/**
	 * Returns !f(n).
	 * 
	 * @param int $t The value of n
	 * @return int The negation of applying $t to this token
	 */
	public function apply($t) {
		$value = $this->value;
		if(!is_int($value)) {
			$value = $value->apply($t);
		}
		return (int) !$value;
	}

	/**
	 * Removes the next token from the scope.
	 * 
	 * @param int $i The current token index
	 * @param array $tokens The tokens being parsed
	 * @throws \gettext\pluralparser\ParseException If this token cannot be resolved
	 */
	public function resolve(&$i = 0, array &$tokens = null) {
		if(!$this->isResolved()) {
			parent::resolve($i, $tokens);
			if($i + 1 < count($tokens)) {
				$this->value = array_splice($tokens, $i + 1, 1)[0];
				if(!is_int($this->value)) {
					$this->value->resolve($i, $tokens);
				}
			} else {
				throw new \gettext\pluralparser\ParseException('No value to negate');
			}
		}
	}

	/**
	 * Collapses this tokens's value.
	 * 
	 * @return NegateToken This token
	 */
	public function collapse() {
		if(!is_int($this->value)) {
			$this->value = $this->value->collapse();
		}
		return $this;
	}

	public function __toString() {
		return '!' + $this->value;
	}
}