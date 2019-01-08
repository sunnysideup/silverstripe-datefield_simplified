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

class SimpleDateFieldController extends Controller
{
    private static $allowed_actions = array(
        "ajaxvalidation" => true
    );

    private static $url = 'formfields-simpledatefield';

    /**
     *
     * @param HTTPRequest
     * @return String (JSON)
     */
    public function ajaxvalidation($request)
    {
        $rawInput = '';
        if (isset($_GET["value"])) {
            $rawInput = ($_GET["value"]);
        }
        $obj = Injector::inst()->get(SimpleDateField::class, $asSingleton = true, array("temp", "temp"));
        return $obj->ConverToFancyDate($rawInput);
    }
}
