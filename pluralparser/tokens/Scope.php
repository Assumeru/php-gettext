<?php
namespace gettext\pluralparser\tokens;

class Scope extends Token {
	/**
	 * @var array An array of tokens
	 */
	private $tokens;
	/**
	 * @var Scope This scope's parent
	 */
	private $parent;

	/**
	 * Constructs a new scope.
	 * 
	 * @param Scope $parent This scope's parent
	 */
	public function __construct(Scope $parent = null) {
		parent::__construct(0);
		$this->parent = $parent;
	}

	/**
	 * Resolves all tokens in this scope.
	 *
	 * @param int $i The current token index
	 * @param array $tokens The tokens being parsed
	 * @throws \gettext\pluralparser\ParseException If this token cannot be resolved
	 */
	public function resolve(&$i = 0, array &$tokens = null) {
		if(!$this->isResolved()) {
			parent::resolve($i, $tokens);
			$this->resolve0();
		}
	}

	private function resolve0() {
		$next = defined('PHP_INT_MIN') ? PHP_INT_MIN : -PHP_INT_MAX;
		do {
			$min = $next;
			for($i = 0; $i < count($this->tokens); $i++) {
				$token = $this->tokens[$i];
				if($token instanceof Token) {
					if($token->getPrecedence() <= $min) {
						$token->resolve($i, $this->tokens);
					} else if($next != $min) {
						$next = min($next, $token->getPrecedence());
					} else {
						$next = $token->getPrecedence();
					}
				}
			}
		} while($next != $min);
		if(count($this->tokens) > 4) {
			$this->parseTenaryScope($this->tokens);
		}
		$this->checkExpression();
	}

	private function parseTenaryScope(array &$tokens) {
		if($tokens[1] !== '?') {
			throw new \gettext\pluralparser\ParseException('? not found');
		}
		$resultTrue = new Scope();
		$resultFalse = new Scope();
		$token = new ConditionalToken($tokens[0], $resultTrue, $resultFalse);
		$current = $resultTrue;
		$depth = 0;
		for($i = 2; $i < count($tokens); $i++) {
			$t = $tokens[$i];
			if($t === '?') {
				$depth++;
			} else if($t === ':') {
				if($depth === 0) {
					if($current == $resultTrue) {
						$current = $resultFalse;
						continue;
					} else {
						throw new \gettext\pluralparser\ParseException('Conditional mismatch');
					}
				}
				$depth--;
			}
			$current->add($t);
		}
		if(count($resultTrue->tokens) > 4) {
			$this->parseTenaryScope($resultTrue->tokens);
		}
		if(count($resultFalse->tokens) > 4) {
			$this->parseTenaryScope($resultFalse->tokens);
		}
		array_splice($tokens, 0, count($tokens), [$token]);
	}

	private function checkExpression() {
		if(count($this->tokens) != 1) {
			throw new \gettext\pluralparser\ParseException('Scope does not resolve to a single expression');
		}
	}

	/**
	 * @param int|string|Token $token A token to add to this scope
	 */
	public function add($token) {
		$this->tokens[] = $token;
	}

	/**
	 * @return Scope This scope's parent or null if it has no parent
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Calculates f(n) for the first token in this scope.
	 * 
	 * @param int $t The value of n
	 * @return int The result of applying $t to this scope
	 */
	public function apply($t) {
		$this->checkExpression();
		$token = $this->tokens[0];
		if(!is_int($token)) {
			$token = $token->apply($t);
		}
		return $token;
	}

	/**
	 * Collapses the first token in this scope.
	 * 
	 * @return int|Token The first token in this scope
	 */
	public function collapse() {
		$this->checkExpression();
		$token = $this->tokens[0];
		if(!is_int($token)) {
			$token = $token->collapse();
		}
		return $token;
	}

	public function __toString() {
		return '(' . implode(', ', $this->tokens) . ')';
	}
}