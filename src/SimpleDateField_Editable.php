<?php


namespace Sunnysideup\DatefieldSimplified;

use SilverStripe\UserForms\Model\EditableFormField;
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
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;

class SimpleDateField_Editable extends EditableFormField
{
    private static $table_name = 'SimpleDateField_Editable';

    private static $db = array(
        "ShowCalendar" => "Boolean",
        "OnlyPastDates" => "Boolean",
        "OnlyFutureDates" => "Boolean",
        "MonthBeforeDay" => "Boolean",
        "ExplanationForEnteringDates" => "Varchar(120)"
    );

    private static $singular_name = 'Simple Date Field';

    private static $plural_name = 'Simple Date Fields';

    public function Icon()
    {
        return 'userforms/images/editabledatefield.png';
    }

    public function canEdit($member = null)
    {
        return true;
    }

    public function getFieldConfiguration()
    {
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

    public function getFormField()
    {
        $field = new SimpleDateField($this->Name, $this->Title);
        if ($this->getSetting('ShowCalendar')) {
            $field->setConfig("showcalendar", true);
        }
        if ($this->getSetting('OnlyPastDates')) {
            $field->setConfig("max", "today");
            Config::modify()->update(SimpleDateField::class, "placeholder_value", '31 jan 1974');
        } elseif ($this->getSetting('OnlyFutureDates')) {
            $field->setConfig("min", "today");
            Config::modify()->update(SimpleDateField::class, "placeholder_value", '31 jan 2023');
        }
        if ($this->getSetting('MonthBeforeDay')) {
            $field->setConfig("dateformat", 'l F j Y');
            Config::modify()->update(SimpleDateField::class, "default_fancy_date_format", 'l F j Y');
            Config::modify()->update(SimpleDateField::class, "month_before_day", true);
        } else {
            Config::modify()->update(SimpleDateField::class, "default_fancy_date_format", 'l j F Y');
            Config::modify()->update(SimpleDateField::class, "month_before_day", false);
        }
        if ($this->getSetting('ExplanationForEnteringDates')) {
            $field->setDescription($this->getSetting('ExplanationForEnteringDates'));
        }
        return $field;
    }
}
