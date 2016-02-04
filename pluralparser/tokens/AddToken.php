<?php
namespace gettext\pluralparser\tokens;

class AddToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_ADD_SUBTRACT, '+');
	}

	protected function apply2($lhs, $rhs) {
		return $lhs + $rhs;
	}
}