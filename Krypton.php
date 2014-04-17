<?php
/**
 * Converter from selenium to arsenic
 *
 * @author Lukes Hemzal
 */
class Krypton {
	private static $xpath;
	private static $filePattern = '/(\w*).html$/';

	/**
	 * @param string $path
	 * @param string $className
	 * @return string
	 */
	public static function convertDirectory($path, $className = 'Test') {
		$lines = ['<?php', 'class ' . $className . ' {'];
		if (is_dir($path)) {
			if ($directory = opendir($path)) {
				while (($file = readdir($directory)) !== false) {
					if (preg_match(self::$filePattern, $file)) {
						$lines[] = self::convertFile($path . '/' . $file, 1);
					}
				}
			}
		}
		$lines[] = '}';
		return implode("\n", $lines);
	}

	/**
	 * @param string $path
	 * @param integer $indentation
	 * @return string
	 */
	public static function convertFile($path, $indentation = 0) {
		$doc = new DOMDocument();
		$doc->loadHTMLFile($path);
		self::$xpath = new DOMXPath($doc);

		$rows = self::$xpath->query('//tbody/tr');
		$lines = [str_repeat("\t", $indentation) . 'public static function ' . self::getFunctionName($path) . '() {'];
		foreach ($rows as $row) {
			$lines[] = str_repeat("\t", $indentation + 1) . self::convertRow($row);
		}
		$lines[] = str_repeat("\t", $indentation) . '}';
		return implode("\n", $lines);
	}

	/**
	 * @param DOMElement $row
	 * @return string
	 */
	private static function convertRow(DOMElement $row) {
		$data = self::$xpath->query('td', $row);
		$function = null;
		$arguments = [];
		foreach ($data as $index => $node) {
			$value = $node->nodeValue;
			switch ($index) {
				case 0:
					$function .= $value;
					break;
				default:
					if ($value !== "") {
						$arguments[] = "'" . str_replace(["'"], ['"'], $value). "'";
					}
			}
		}

		return 'Arsenic::' . $function . '(' . implode(', ', $arguments) . ');';
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private static function getFunctionName($path) {
		if (preg_match(self::$filePattern, $path, $matches)) {
			return $matches[1];
		}
	}
}
