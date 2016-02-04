<?php
namespace gettext\pluralparser\tokens;

class AndToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_AND, '&&');
	}

	protected function apply2($lhs, $rhs) {
		return $lhs && $rhs;
	}
}