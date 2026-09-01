<?php

namespace WPCourses;

if ( ! defined( 'ABSPATH' ) ) exit;

class Helpers {
	public static function format_course_date($raw) {
		if (!$raw) {
			return '';
		}
		
		$date = \DateTime::createFromFormat('Ymd', $raw);
		
		if (!$date) {
			$date = \DateTime::createFromFormat('d/m/Y', $raw);
		}
		
		if (!$date) {
			return $raw;
		}
		
		
		$months = array(
			1 => 'sty', 2 => 'lut', 3 => 'mar', 4 => 'kwi',
			5 => 'maj', 6 => 'cze', 7 => 'lip', 8 => 'sie',
			9 => 'wrz', 10 => 'paź', 11 => 'lis', 12 => 'gru',
		);
		
		$day = (int)$date->format('j');
		$month = $months[(int)$date->format('n')];
		$year = $date->format('Y');
		
		return $day . ' ' . $month . ' ' . $year;
	}
	
	public static function get_lowest_price($price_group) {
		if (!is_array($price_group)) {
			return null;
		}
		
		$labels = array(
			'cena_dla_nowych_osob' => 'cena dla nowych osób',
			'cena_dla_nowych_osob_w_przedplacie' => 'cena dla nowych osób w przedpłacie',
			'cena_kurs_podstawowy' => 'cena standardowa',
			'cena_dla_osob_po_kursie_sprzed_2015_r' => 'cena dla osób po kursie sprzed 2015 r.',
			'cena_dla_osob_powtarzajacych' => 'cena dla osób powtarzających',
			'cena_po_dzieci' => 'cena dla osób po kursie dla dzieci',
		);
		
		$lowest = null;
		$lowest_label = '';
		
		foreach ($labels as $key => $label) {
			if (!isset($price_group[$key]) || $price_group[$key] === '' || $price_group[$key] === null) {
				continue;
			}
			
			$value = floatval($price_group[$key]);
			
			if ($lowest === null || $value < $lowest) {
				$lowest = $value;
				$lowest_label = $label;
			}
		}
		
		if ($lowest === null) {
			return null;
		}
		
		return array('cena' => $lowest, 'etykieta' => $lowest_label);
	}
	
	public static function get_all_prices( $price_group ) {
		if ( ! is_array( $price_group ) ) return array();
		
		$labels = array(
			'cena_dla_nowych_osob' => 'Cena dla nowych osób',
			'cena_dla_nowych_osob_w_przedplacie' => 'Cena dla nowych osób w przedpłacie',
			'cena_kurs_podstawowy' => 'Osoby po kursie podstawowym',
			'cena_dla_osob_po_kursie_sprzed_2015_r' => 'Osoby po kursie 3-dniowym sprzed 2015 r.',
			'cena_dla_osob_powtarzajacych' => 'Osoby powtarzające',
			'cena_po_dzieci' => 'Osoby po kursie dla dzieci',
		);
		
		$rows = array();
		foreach ( $labels as $key => $label ) {
			if ( ! isset( $price_group[ $key ] ) || $price_group[ $key ] === '' || $price_group[ $key ] === null ) {
				continue;
			}
			$rows[] = array( 'label' => $label, 'amount' => floatval( $price_group[ $key ] ) );
		}
		
		return $rows;
	}
	
	public static function count_days( $raw_from, $raw_to ) {
		if ( ! $raw_from ) {
			return 0;
		}
		
		$from = \DateTime::createFromFormat( 'Ymd', $raw_from );
		
		if ( ! $from ) {
			return 0;
		}
		
		if ( ! $raw_to ) {
			return 1;
		}
		
		$to = \DateTime::createFromFormat( 'Ymd', $raw_to );
		
		if ( ! $to ) {
			return 1;
		}
		
		$diff = $from->diff( $to );
		return $diff->days + 1;
	}
}
