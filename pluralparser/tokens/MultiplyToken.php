<?php
namespace gettext\pluralparser\tokens;

class DivideToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_MULT_DIVIDE, '*');
	}

	public function apply2($lhs, $rhs) {
		return $lhs * $rhs;
	}
}