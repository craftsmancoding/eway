<?php
/*
Some utility functions for Eway
*/
class Eway {

	/**
	 * Extract a value from XML, bookmarked by $start_tag & $end_tag
	 *
	 * @param string $string
	 * @param string $start_tag e.g. <result>
	 * @param string $end_tag e.g. </result>
	 * @return string
	 */
	static function fetch_data($string, $start_tag, $end_tag) {
		$position = stripos($string, $start_tag);
		$str = substr($string, $position);
		$str_second = substr($str, strlen($start_tag));
		$second_positon = stripos($str_second, $end_tag);
		$str_third = substr($str_second, 0, $second_positon);
		$fetch_data = trim($str_third);
		
		return $fetch_data;
	}


	/**
	 * Get a value out of an array. Priority is given to $array1, but $array2 is also searched.
	 *
	 * @param array $array1
	 * @param array $array2
	 * @param string $key
	 * @return string
	 */
	static function get($array1,$array2,$key) {
		if (isset($array1[$key])) {
			return $array1[$key];
		}
		elseif (isset($array2[$key])) {
			return $array2[$key];
		}
		return '';
	}

}

/*EOF*/