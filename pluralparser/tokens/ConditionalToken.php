<?php
namespace gettext\pluralparser\tokens;

class ConditionalToken extends Token {
	/**
	 * @var int|Token The condition
	 */
	private $condition;
	/**
	 * @var int|Token The token to return if the condition is true
	 */
	private $resultTrue;
	/**
	 * @var int|Token The token to return if the condition is false
	 */
	private $resultFalse;

	/**
	 * Constructs a new conditional token.
	 * 
	 * @param Token $condition The condition to check
	 * @param Token $resultTrue The token to use when the condition returns true
	 * @param Token $resultFalse The token to use when the condition returns false
	 */
	public function __construct(Token $condition, Token $resultTrue, Token $resultFalse) {
		parent::__construct(static::PREC_TERNARY_IF);
		$this->condition = $condition;
		$this->resultTrue = $resultTrue;
		$this->resultFalse = $resultFalse;
	}

	/**
	 * Returns t(n) if c(n) is true, otherwise returns f(n).
	 * 
	 * @param int $t The value of n
	 * @return int The result of applying $t to this token
	 */
	public function apply($t) {
		return $this->apply2($this->condition, $t) ? $this->apply2($this->resultTrue, $t) : $this->apply2($this->resultFalse, $t);
	}

	private function apply2($var, $t) {
		if(is_int($var)) {
			return $var;
		}
		return $var->apply($t);
	}

	/**
	 * Collapses the condition, true, and false tokens.
	 * 
	 * @return ConditionalToken This token
	 */
	public function collapse() {
		if(!is_int($this->condition)) {
			$this->condition = $this->condition->collapse();
		}
		if(!is_int($this->resultTrue)) {
			$this->resultTrue = $this->resultTrue->collapse();
		}
		if(!is_int($this->resultFalse)) {
			$this->resultFalse = $this->resultFalse->collapse();
		}
		return $this;
	}

	public function __toString() {
		return $this->condition . ' ? ' . $this->resultTrue . ' : ' . $this->resultFalse;
	}
}