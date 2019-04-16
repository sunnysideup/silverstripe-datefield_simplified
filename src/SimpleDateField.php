<?php

namespace Sunnysideup\DatefieldSimplified;

use SilverStripe\View\Requirements;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use Sunnysideup\DatefieldSimplified\SimpleDateFieldController;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\DateField;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\DatefieldSimplified\SimpleDateField;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;

/**
 * @author nicolaas [at] sunnysideup.co.nz
 * To Do:
 * 1. write documenation
 * 2. change $config into NON-static
 */


class SimpleDateField extends DateField
{

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
    private static $default_fancy_date_format = 'j F Y';

    /**
     * What would you like the place holder value to be?
     * @var String
     */
    private static $placeholder_value = '31 jan 2123';

    public function __construct($name, $title = null, $value = null, $form = null, $config = array())
    {
        parent::__construct($name, $title, $value, $form);
        $this->setConfig("dmyfields", false);
        $this->setConfig("showcalendar", false);
    }

    public function Field($options = array())
    {
        //GENERAL
        Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
        Requirements::javascript("sunnysideup/datefield_simplified: client/javascript/SimpleDateField.js");
        $this->addExtraClass("simpledatefield");
        $this->setAttribute("placeholder", $this->Config()->get("placeholder_value"));
        $html = parent::Field($options);
        $fieldID = $this->id();
        $url = Convert::raw2js(Director::absoluteBaseURL().Config::inst()->get(SimpleDateFieldController::class, "url")."/ajaxvalidation/");
        $objectID = $fieldID."_OBJECT";
        Requirements::customScript(
            "
            var $objectID = new SimpleDateFieldAjaxValidationAPI('".$fieldID."');
            $objectID.init();
            $objectID.setVar('url', '$url');
            ",
            'func_SimpleDateField'.$fieldID
        );
        return $html;
    }

    /**
     * Sets the internal value to ISO date format.
     *
     * @param String|Array $val
     */
    public function setValue($val, $data = null)
    {
        $date = $this->ConvertToTSorERROR($val);
        if (is_numeric($date)  && intval($date) == $date && $date > 0) {
            $val = date("Y-m-d", $date);
        } else {
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
    public function ConvertToTSorERROR($rawInput)
    {
        $settings = $this->getConfig();
        if (!isset($settings['dateformat'])) {
            $settings['dateformat'] = $this->Config()->get("default_fancy_date_format");
        }
        $tsOrError = null;
        if ($this->Config()->get("month_before_day")) {
            $cleanedInput = str_replace("-", "/", $rawInput);
        } else {
            $cleanedInput = str_replace("/", "-", $rawInput);
        }
        if ($cleanedInput) {
            $tsOrError = intval(strtotime($cleanedInput));
        }
        if (is_numeric($tsOrError) && $tsOrError > 0) {
            if (isset($settings['min']) && $minDate = $settings['min']) {
                $minDate = strtotime($minDate);
                if ($minDate) {
                    if ($minDate > $tsOrError) {
                        $tsOrError = sprintf(_t('SimpleDateField.VALIDDATEMINDATE', "Your date can not be before %s."), date($settings['dateformat'], $minDate));
                    }
                }
            }
            if (isset($settings['max']) && $maxDate = $settings['max']) {
                // ISO or strtotime()
                $maxDate = strtotime($maxDate);
                if ($maxDate) {
                    if ($maxDate < $tsOrError) {
                        $tsOrError = sprintf(_t('SimpleDateField.VALIDDATEMAXDATE', "Your date can not be after %s."), date($settings['dateformat'], $maxDate));
                    }
                }
            }
        }
        if (!$tsOrError) {
            if (!trim($rawInput)) {
                $tsOrError = sprintf(_t('SimpleDateField.VALIDDATEDATE_NOENTRY', "You need to enter a date."), $rawInput);
            } else {
                $tsOrError = sprintf(_t('SimpleDateField.VALIDDATEDATE_CANTMAKEDATE', "We did not understand the date you entered '%s'."), $rawInput);
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
    public function ConverToFancyDate($rawInput)
    {
        $settings = $this->getConfig();
        $tsOrError = $this->ConvertToTSorERROR($rawInput);
        if (is_numeric($tsOrError) && intval($tsOrError)) {
            $array = array(
                "value" => date($this->Config()->get("default_fancy_date_format"), $tsOrError),
                "success" => 1
            );
        } else {
            $array = array(
                "value" =>  $tsOrError,
                "success" => 0
            );
        }
        return Convert::raw2json($array);
    }
}
