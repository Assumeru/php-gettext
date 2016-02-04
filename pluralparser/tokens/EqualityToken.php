<?php
namespace gettext\pluralparser\tokens;

class EqualityToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_EQUALITY, '==');
	}

	public function apply2($lhs, $rhs) {
		return $lhs == $rhs;
	}
}