<?php
namespace gettext\pluralparser\tokens;

abstract class Token {
	const PREC_UNARY = 1;
	const PREC_MULT_DIVIDE = 2;
	const PREC_ADD_SUBTRACT = 3;
	const PREC_RELATIONAL = 5;
	const PREC_EQUALITY = 6;
	const PREC_AND = 10;
	const PREC_OR = 11;
	const PREC_TERNARY_IF = 12;
	/**
	 * @var int Resolution precedence, lower values are resolved first
	 */
	private $precedence;
	/**
	 * @var boolean
	 */
	private $resolved = false;

	/**
	 * Constructs a new Token.
	 * 
	 * @param int $precedence Resolution precedence, lower values are resolved first
	 */
	protected function __construct($precedence) {
		$this->precedence = $precedence;
	}

	/**
	 * @return int Resolution precedence, lower values are resolved first
	 */
	public function getPrecedence() {
		return $this->precedence;
	}

	/**
	 * @return boolean True if the resolve method has been called
	 */
	public function isResolved() {
		return $this->resolved;
	}

	/**
	 * Calculates f(n) for this token.
	 * 
	 * @param int $t The value of variable n
	 * @return int The result of applying $t to this token
	 */
	public abstract function apply($t);

	/**
	 * Allows this token to modify its scope.
	 * 
	 * @param int $i The current token index
	 * @param array $tokens The tokens being parsed
	 * @throws \gettext\pluralparser\ParseException If this token cannot be resolved
	 */
	public function resolve(&$i = 0, array &$tokens = null) {
		$this->resolved = true;
	}

	/**
	 * Removes superfluous tokens from the tree.
	 * 
	 * @return Token The first relevant token in this tree
	 */
	public function collapse() {
		return $this;
	}
}