<?php
namespace gettext\pluralparser;

class Parser {
	/**
	 * @var array Array matching tokens to their classes
	 */
	private static $TOKENS = [
		'!' => 'tokens\\NegateToken',
		'>' => 'tokens\\MoreToken',
		'<' => 'tokens\\LessToken',
		'/' => 'tokens\\DivideToken',
		'*' => 'tokens\\MultiplyToken',
		'%' => 'tokens\\ModuloToken',
		'+' => 'tokens\\AddToken',
		'-' => 'tokens\\SubtractToken',
		'==' => 'tokens\\EqualityToken',
		'!=' => 'tokens\\InequalityToken',
		'>=' => 'tokens\\EqualMoreToken',
		'<=' => 'tokens\\EqualLessToken',
		'||' => 'tokens\\OrToken',
		'&&' => 'tokens\\AndToken'
	];
	/**
	 * @var string Input string to parse
	 */
	private $input;
	/**
	 * @var int Current position in the input string
	 */
	private $i;

	/**
	 * Constructs a Parser that parses gettext plural expressions.
	 * Supported operators: !, >, <, /, *, %, +, -, ==, !=, >=, <=, ||, &&, ? :
	 * 
	 * @param string $input
	 * @throws \InvalidArgumentException If the string contains illegal characters
	 */
	public function __construct($input) {
		if(!preg_match('/^([\sn\=\+\-\>\<\(\)\%\*\/\!\?\:\|\&\^0-9]+)$/', $input)) {
			throw new \InvalidArgumentException('Plurals string contains illegal characters');
		}
		$this->input = preg_replace('/\s/', '', $input);
	}

	/**
	 * Constructs an anonymous function from the input string.
	 * 
	 * @return \Closure A function of the form <code>function(int $n) : int</code>
	 * @throws ParseException If the input cannot be parsed
	 */
	public function parse() {
		$current = new tokens\Scope(null);
		$var = new tokens\VariableToken();
		for($this->i = 0; $this->i < strlen($this->input); $this->i++) {
			$c = $this->input[$this->i];
			if($c == '(') {
				$open = new tokens\Scope($current);
				$current->add($open);
				$current = $open;
			} else if($c == ')') {
				if($current->getParent() == null) {
					throw new ParseException('Brace mismatch');
				}
				$current = $current->getParent();
			} else if($c == '?' || $c == ':') {
				$current->add($c);
			} else if($c == 'n') {
				$current->add($var);
			} else if($this->isDecimal($c)) {
				$current->add($this->parseInt());
				$this->i--;
			} else {
				$key = substr($this->input, $this->i, 2);
				if(isset(self::$TOKENS[$key])) {
					$current->add($this->getToken(self::$TOKENS[$key]));
					$this->i++;
				} else if(isset(self::$TOKENS[$c])) {
					$current->add($this->getToken(self::$TOKENS[$c]));
				} else {
					throw new ParseException('Unknown token ' . $c);
				}
			}
		}
		if($current->getParent() != null) {
			throw new ParseException('Brace mismatch');
		}
		$current->resolve();
		return $this->getFunction($current->collapse());
	}

	private function getToken($token) {
		$class = __NAMESPACE__ . '\\' . $token;
		return new $class();
	}

	private function getFunction(tokens\Token $token) {
		return static function($n) use($token) {
			return $token->apply($n);
		};
	}

	private function isDecimal($c) {
		return $c >= '0' && $c <= '9';
	}

	private function parseInt() {
		$start = $this->i;
		while($this->i < strlen($this->input) && $this->isDecimal($this->input[$this->i])) {
			$this->i++;
		}
		return (int) substr($this->input, $start, $this->i - $start);
	}
}