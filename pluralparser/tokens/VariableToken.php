<?php
namespace gettext\pluralparser\tokens;

class VariableToken extends Token {
	public function __construct() {
		parent::__construct(0);
	}

	/**
	 * Returns n.
	 * 
	 * @param int $t The value of n
	 * @return int The value of $t
	 */
	public function apply($t) {
		return $t;
	}

	public function __toString() {
		return 'n';
	}
}