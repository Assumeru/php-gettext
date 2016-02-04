<?php
namespace gettext\pluralparser\tokens;

class LessToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_RELATIONAL, '<');
	}

	public function apply2($lhs, $rhs) {
		return $lhs < $rhs;
	}
}