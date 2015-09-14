<?php
namespace gettext;

class MO {
	const MAGIC = 0x950412de;
	const LITTLE = 'V';
	const BIG = 'N';

	private $endianess;
	private $revision;
	private $numStrings;
	private $numPlurals = 2;
	/**
	 * @var int Offset of the original strings table
	 */
	private $originalTable;
	/**
	 * @var int Offset of the translated strings table
	 */
	private $translationTable;
	/**
	 * @var array The headers contained in this MO
	 */
	private $headers = array();
	/**
	 * @var array Translations by key
	 */
	private $translations;
	/**
	 * @var \Closure A closure that calculates the index of a translation
	 */
	private $plural;

	/**
	 * Creates a new MO from a string.
	 * 
	 * @param string $data
	 * @throws \InvalidArgumentException If $data is not a MO file
	 */
	public function __construct($data) {
		$this->parseEndianess($data);
		$this->parseRevision($data);
		$this->numStrings = $this->getInt($data, 8);
		$this->originalTable = $this->getInt($data, 12);
		$this->translationTable = $this->getInt($data, 16);
		$this->parseTranslations($data);
		$this->setUpPlural();
	}

	private function parseEndianess($data) {
		$magic = substr($data, 0, 4);
		if($magic == pack('V', self::MAGIC)) {
			$this->endianess = self::LITTLE;
		} else if($magic == pack('N', self::MAGIC)) {
			$this->endianess = self::BIG;
		} else {
			throw new \InvalidArgumentException('Invalid magic number');
		}
	}

	private function getInt($data, $index) {
		$unpack = unpack($this->endianess, substr($data, $index, 4));
		if(!isset($unpack[1])) {
			throw new \InvalidArgumentException('Could not read 4 bytes at offset '.$index);
		}
		return $unpack[1];
	}

	private function parseRevision($data) {
		$revision = $this->getInt($data, 4);
		if($revision !== 0) {
			throw new \InvalidArgumentException('Unsupported revision: '.$revision);
		}
		$this->revision = $revision;
	}

	private function parseTranslations($data) {
		for($n = 0; $n < $this->numStrings; $n++) {
			$originalTable = $this->originalTable + 8 * $n;
			$originalLength = $this->getInt($data, $originalTable);
			$originalOffset = $this->getInt($data, $originalTable + 4);
			$translationTable = $this->translationTable + 8 * $n;
			$translationLength = $this->getInt($data, $translationTable);
			$translationOffset = $this->getInt($data, $translationTable + 4);
			$original = substr($data, $originalOffset, $originalLength);
			$translation = substr($data, $translationOffset, $translationLength);
			$this->addEntry($original, $translation);
		}
	}

	private function addEntry($original, $translation) {
		if($original === '') {
			//Account for fake newlines
			$headers = explode("\n", str_replace('\n', "\n", $translation));
			foreach($headers as $header) {
				$parts = explode(':', $header, 2);
				if(isset($parts[1])) {
					$this->headers[trim($parts[0])] = trim($parts[1]);
				}
			}
		} else {
			$parts = explode(chr(0), $original);
			$this->translations[$parts[0]] = explode(chr(0), $translation);
		}
	}

	private function setUpPlural() {
		if(!isset($this->headers['Plural-Forms'])) {
			return;
		}
		if(preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s*plural\s*=\s*(.+)$/', $this->headers['Plural-Forms'], $matches)) {
			$this->numPlurals = (int)$matches[1];
			eval('$this->plural = function($amount) {
				return (int)('.str_replace(array('n', ';'), array('$amount', ''), $matches[2]).');
			};');
		}
	}

	/**
	 * Returns a translation of the given string.
	 * 
	 * @param string $singular The string to translate
	 * @param string $plural Optional plural version of the string
	 * @param int $amount Optional amount to select a plural for
	 * @param string $context Optional context
	 * @return string Either a translated string or the provided fallback
	 */
	public function translate($singular, $plural = null, $amount = null, $context = null) {
		$index = 0;
		if($plural !== null && $amount !== null) {
			$index = $this->getPluralIndex($amount);
		}
		$key = $context !== null ? $context . chr(4) . $singular : $singular;
		$translation = $this->getTranslation($key, $index);
		if($translation === false) {
			if($index > 0) {
				return $plural;
			}
			return $singular;
		}
		return $translation;
	}

	private function getPluralIndex($amount) {
		if($this->plural !== null) {
			$plural = $this->plural;
			$index = $plural($amount);
		} else {
			$index = $amount === 1 ? 0 : 1;
		}
		if($index >= $this->numPlurals) {
			return 0;
		}
		return $index;
	}

	private function getTranslation($key, $index = 0) {
		if(isset($this->translations[$key]) && isset($this->translations[$key][$index])) {
			return $this->translations[$key][$index];
		}
		return false;
	}

	/**
	 * Adds the translations from another MO to this MO.
	 * 
	 * @param MO $other
	 */
	public function merge(MO $other) {
		$this->numStrings += $other->numStrings;
		$this->translations = array_merge($this->translations, $other->translations);
	}

	/**
	 * Returns the number of translated strings contained in this MO.
	 * 
	 * @return int
	 */
	public function getLength() {
		return $this->numStrings;
	}
}