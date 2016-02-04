<?php
namespace gettext\pluralparser\tokens;

abstract class BinaryToken extends Token {
	/**
	 * @var int|Token The token to the left of this token
	 */
	private $lhs;
	/**
	 * @var int|Token The token to the right of this token
	 */
	private $rhs;
	/**
	 * @var string String representation of this token's operator
	 */
	private $operator;

	/**
	 * Constructs a new token surrounded by tokens.
	 * 
	 * @param int $precedence Resolution precedence, lower values are resolved first
	 * @param string $operator This token's operator
	 */
	protected function __construct($precedence, $operator) {
		parent::__construct($precedence);
		$this->operator = ' ' . $operator . ' ';
	}

	/**
	 * Calculates f(lhs(n), rhs(n)) for this token.
	 * 
	 * @param int $t The value of n
	 * @return int The result of applying $t to this token
	 */
	public function apply($t) {
		$lhs = $this->lhs;
		$rhs = $this->rhs;
		if(!is_int($lhs)) {
			$lhs = $lhs->apply($t);
		}
		if(!is_int($rhs)) {
			$rhs = $rhs->apply($t);
		}
		return (int) $this->apply2($lhs, $rhs);
	}

	/**
	 * Calculates f(lhs, rhs) for this token.
	 * 
	 * @param int $lhs The result of applying n to the left argument of this token
	 * @param int $rhs The result of applying n to the right argument of this token
	 * @return int The result of f(lhs, rhs)
	 */
	protected abstract function apply2($lhs, $rhs);

	/**
	 * Removes the left and right tokens from the scope.
	 * 
	 * @param int $i The current token index
	 * @param array $tokens The tokens being parsed
	 * @throws \gettext\pluralparser\ParseException If this token cannot be resolved
	 */
	public function resolve(&$i = 0, array &$tokens = null) {
		if(!$this->isResolved()) {
			parent::resolve($i, $tokens);
			if($i > 0 && $i + 1 < count($tokens)) {
				$this->rhs = array_splice($tokens, $i + 1, 1)[0];
				$this->lhs = array_splice($tokens, $i - 1, 1)[0];
				if(!is_int($this->rhs)) {
					$this->rhs->resolve($i, $tokens);
				}
				$i--;
			} else {
				throw new \gettext\pluralparser\ParseException($this->operator . 'without arguments');
			}
		}
	}

	/**
	 * Collapses the left and right tokens.
	 * 
	 * @return BinaryToken This token
	 */
	public function collapse() {
		if(!is_int($this->lhs)) {
			$this->lhs = $this->lhs->collapse();
		}
		if(!is_int($this->rhs)) {
			$this->rhs = $this->rhs->collapse();
		}
		return $this;
	}

	public function __toString() {
		return $this->lhs . $this->operator . $this->rhs;
	}
}