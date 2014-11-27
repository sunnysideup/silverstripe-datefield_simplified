<?php

/**
 * @author nicolaas [at] sunnysideup.co.nz
 * To Do:
 * 1. write documenation
 * 2. change $config into NON-static
 */


class SimpleDateField extends DateField {

	/**
	 * Americans should set this to TRUE.
	 * For people who write
	 * 10-Nov-2012, to mean 10 November, you an leave it as FALSE
	 * For sites with customers from all over, you will have to tell them the
	 * preferred format. You can use the placeholder value for this.
	 *
	 * @var Boolean
	 */
	private static $month_before_day = false;

	/**
	 * The PHP date function formatting for showing the final date.
	 * @var String
	 */
	private static $default_fancy_date_format = 'D j F Y';

	/**
	 * What would you like the place holder value to be?
	 * @var String
	 */
	private static $placeholder_value = '31 jan 2123';

	function __construct($name, $title = null, $value = null, $form = null, $config = array()) {
		parent::__construct($name, $title, $value, $form);
		$this->setConfig("dmyfields", false);
		$this->setConfig("showcalendar", false);
	}

	function Field($options = array()) {
		//GENERAL
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript("datefield_simplified/javascript/SimpleDateField.js");
		$this->addExtraClass("simpledatefield");
		$this->setAttribute("placeholder", $this->Config()->get("placeholder_value"));
		$html = parent::Field($options);
		$fieldID = $this->id();
		$url = Convert::raw2js(Director::absoluteBaseURL().Config::inst()->get("SimpleDateField_Controller", "url")."/ajaxvalidation/");
		Requirements::customScript("SimpleDateFieldAjaxValidation.setupField('$fieldID', '$url');", 'func_SimpleDateField'.$fieldID);
		return $html;
	}

	/**
	 * Sets the internal value to ISO date format.
	 *
	 * @param String|Array $val
	 */
	public function setValue($val) {
		$date = $this->ConvertToTSorERROR($val);
		if(is_numeric($date)  && intval($date) == $date && $date > 0) {
			$val = date("Y-m-d", $date);
		}
		else {
			$val = null;
		}
		return parent::setValue($val);
	}


	/**
	 * changes the raw input into TimeStamp Date OR ERROR String
	 *
	 * @param String $rawInput
	 *
	 * @return Int | String
	 *
	 */
	public function ConvertToTSorERROR($rawInput) {
		$settings = $this->getConfig();
		if(!isset($settings['dateformat'])) {
			$settings['dateformat'] = $this->Config()->get("default_fancy_date_format");
		}
		$tsOrError = null;
		if($this->Config()->get("month_before_day")) {
			$cleanedInput = str_replace("-", "/", $rawInput);
		}
		else {
			$cleanedInput = str_replace("/", "-", $rawInput);
		}
		if($cleanedInput) {
			$tsOrError = intval(strtotime($cleanedInput));
		}
		if(is_numeric($tsOrError) && $tsOrError > 0) {
			if(isset($settings['min']) && $minDate = $settings['min']) {
				$minDate = strtotime($minDate);
				if($minDate) {
					if($minDate > $tsOrError) {
						$tsOrError = sprintf(_t('SimpleDateField.VALIDDATEMINDATE', "Your date can not be before %s."),date($settings['dateformat'], $minDate));
					}
				}
			}
			if(isset($settings['max']) && $maxDate = $settings['max']) {
				// ISO or strtotime()
				$maxDate = strtotime($maxDate);
				if($maxDate) {
					if($maxDate < $tsOrError) {
						$tsOrError = sprintf(_t('SimpleDateField.VALIDDATEMAXDATE', "Your date can not be after %s."),date($settings['dateformat'], $maxDate));
					}
				}
			}
		}
		if(!$tsOrError) {
			if(!trim($rawInput)) {
				$tsOrError = sprintf(_t('SimpleDateField.VALIDDATEDATE_NOENTRY', "You need to enter a date."),$rawInput);
			}
			else {
				$tsOrError = sprintf(_t('SimpleDateField.VALIDDATEDATE_CANTMAKEDATE', "We did not understand the date you entered '%s'."),$rawInput);
			}
		}
		return $tsOrError;
	}

	/**
	 * Turns user input into a formatted Date or an Error Message ...
	 *
	 * @param String $rawInput
	 * @return String
	 */
	public function ConverToFancyDate($rawInput) {
		$settings = $this->getConfig();
		$tsOrError = $this->ConvertToTSorERROR($rawInput);
		if(is_numeric($tsOrError) && intval($tsOrError)) {
			$array = array(
				"value" => date($this->Config()->get("default_fancy_date_format"), $tsOrError ),
				"success" => 1
			);
		}
		else {
			$array = array(
				"value" =>  $tsOrError,
				"success" => 0
			);
		}
		return Convert::raw2json($array);
	}

}


class SimpleDateField_Controller extends Controller {

	private static $allowed_actions = array(
		"ajaxvalidation" => true
	);

	private static $url = 'formfields-simpledatefield';

	/**
	 *
	 * @param HTTPRequest
	 * @return String (JSON)
	 */
	public function ajaxvalidation($request) {
		$rawInput = '';
		if(isset($_GET["value"])) {
			$rawInput = ($_GET["value"]);
		}
		$obj = Injector::inst()->get("SimpleDateField", $asSingleton = true, array("temp","temp"));
		return $obj->ConverToFancyDate($rawInput);
	}


}


class SimpleDateField_Editable extends EditableFormField {

	private static $db = array(
		"ShowCalendar" => "Boolean",
		"OnlyPastDates" => "Boolean",
		"OnlyFutureDates" => "Boolean",
		"MonthBeforeDay" => "Boolean",
		"ExplanationForEnteringDates" => "Varchar(120)"
	);

	private static $singular_name = 'Simple Date Field';

	private static $plural_name = 'Simple Date Fields';

	public function Icon() {
		return 'userforms/images/editabledatefield.png';
	}

	public function canEdit($member = null) {
		return true;
	}

	function getFieldConfiguration() {
		$fields = parent::getFieldConfiguration();
		// eventually replace hard-coded "Fields"?
		$baseName = "Fields[$this->ID]";
		$ShowCalendar = ($this->getSetting('ShowCalendar')) ? $this->getSetting('ShowCalendar') : '0';
		$OnlyPastDates = ($this->getSetting('OnlyPastDates')) ? $this->getSetting('OnlyPastDates') : '0';
		$OnlyFutureDates = ($this->getSetting('OnlyFutureDates')) ? $this->getSetting('OnlyFutureDates') : '0';
		$MonthBeforeDay = ($this->getSetting('MonthBeforeDay')) ? $this->getSetting('MonthBeforeDay') : '0';
		$ExplanationForEnteringDates = ($this->getSetting('ExplanationForEnteringDates')) ? $this->getSetting('ExplanationForEnteringDates') : '';
		$extraFields = new FieldList(
			new FieldGroup(
				_t('SimpleDateField_Editable.DATESETTINGS', 'Date Settings'),
				new CheckboxField($baseName . "[CustomSettings][ShowCalendar]", "Show Calendar", $ShowCalendar),
				new CheckboxField($baseName . "[CustomSettings][OnlyPastDates]", "Only Past Dates?", $OnlyPastDates),
				new CheckboxField($baseName . "[CustomSettings][OnlyFutureDates]", "Only Future Dates?", $OnlyFutureDates),
				new CheckboxField($baseName . "[CustomSettings][MonthBeforeDay]", "Month before day (e.g. Jan 11 2011)?", $MonthBeforeDay),
				new TextField($baseName . "[CustomSettings][ExplanationForEnteringDates]", "Explanation for entering dates", $ExplanationForEnteringDates)
			)
		);
		$fields->merge($extraFields);
		return $fields;
	}

	public function getFormField() {
		$field = new SimpleDateField($this->Name, $this->Title);
		if($this->getSetting('ShowCalendar')) {
			$field->setConfig("showcalendar", true);
		}
		if($this->getSetting('OnlyPastDates')) {
			$field->setConfig("max", "today");
		}
		elseif($this->getSetting('OnlyFutureDates')) {
			$field->setConfig("min", "today");
		}
		if($this->getSetting('MonthBeforeDay')) {
			$field->setConfig("dateformat", 'l F j Y');
			Config::inst()->set("SimpleDateField","default_fancy_date_format", 'l F j Y');
			Config::inst()->set("SimpleDateField","month_before_day", true);
		}
		else {
			Config::inst()->set("SimpleDateField","default_fancy_date_format", 'l j F Y');
			Config::inst()->set("SimpleDateField","month_before_day", false);
		}
		if($this->getSetting('ExplanationForEnteringDates')) {
			$field->setRightTitle($this->getSetting('ExplanationForEnteringDates'));
		}
		return $field;
	}
}
