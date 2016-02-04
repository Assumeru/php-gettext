<?php
namespace gettext\pluralparser\tokens;

class OrToken extends BinaryToken {
	public function __construct() {
		parent::__construct(static::PREC_OR, '||');
	}

	protected function apply2($lhs, $rhs) {
		return $lhs || $rhs;
	}
}