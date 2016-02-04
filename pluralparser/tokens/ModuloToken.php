<?php
namespace gettext\pluralparser\tokens;

class ModuloToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_MULT_DIVIDE, '%');
	}

	protected function apply2($lhs, $rhs) {
		return $lhs % $rhs;
	}
}