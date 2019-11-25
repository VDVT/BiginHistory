<?php
namespace Bigin\History\Supports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait HistoryFormatDataTypeTrait
{   
    /**
     * [$displayEmpty]
     * @var string
     */
    protected $displayEmpty = '<empty>';

	/**
	 * [$displayAttributes ]
	 * @var Array
	 */
	protected $displayAttributes = [
		'name' => 'Name'
	];

    /**
     * [$relationShipAttributes ]
     * @example 'column_name' => [
     *      'mapTable'  => 'table_name_here',
     *      'mapColumn' => 'column_name_here',
     *      'mapResult' => 'column_result_name_here',
     *      'mapSelect' => 'column_select_name_here',
     *  ]
     * 
     * @var Array
     */
    protected $relationShipAttributes = [];

    /**
     * [$numericAttributes ]
     * @var Array
     */
    protected $ignoreFormatAttributes = [
    ];

    /**
     * [$numericAttributes ]
     * @var Array
     */
    protected $numericAttributes = [
        'column_numeric'
    ];

    /**
     * [$encryptAttributes ]
     * @var Array
     */
    protected $encryptAttributes = [];

    /**
     * [$zipcodeAttributes ]
     * @var Array
     */
    protected $zipcodeAttributes = [];

    /**
     * [$mediaAttributes ]
     * @var Array
     */
    protected $mediaAttributes = [];

    /**
     * [$numericAttributes ]
     * @var Array
     */
    protected $referenceAttributes = [];

    /**
     * [$currencyAttributes]
     * @var Array
     */
    protected $currencyAttributes = [];

	/**
	 * [$typeDateTime ]
	 * @var Array
	 */
	protected $typeDateTime = ['datetime','date'];

	/**
	 * [$typeBoolean ]
	 * @var Array
	 */
	protected $typeBoolean = ['boolean'];

    /**
     * [$percentAttributes]
     * @var Array
     */
    protected $percentAttributes = [];

	/**
     * [formatDateTimeType ]
     * @param  [type] $value     
     * @param  string $formatTime
     * @return mixed
     */
    protected function historyFormatDateTime($value, $formatTime = "m/d/Y")
    {
        if ($value) {

            $timezone = (Auth::check()) ? Auth::user()->timezone : (\Config::get('app.timezone') ? \Config::get('app.timezone') : 'UTC');
            // If this value is an integer, we will assume it is a UNIX timestamp's value
            // and format a Carbon object from this timestamp. This allows flexibility
            // when defining your date fields as they might be UNIX timestamps here.
            if (is_numeric($value))
            {
                $dt = Carbon::createFromTimestamp($value, $timezone);
                return $dt->format($formatTime);
            }

            // If the value is in simply year, month, day format, we will instantiate the
            // Carbon instances from that format. Again, this provides for simple date
            // fields on the database, while still supporting Carbonized conversion.
            elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value))
            {
                $dt =  Carbon::createFromFormat('Y-m-d', $value, $timezone)->startOfDay();
                return $dt->format($formatTime);
            }

            // If the value is in simply year, month, day format, we will instantiate the
            // Carbon instances from that format. Again, this provides for simple date
            // fields on the database, while still supporting Carbonized conversion.
            elseif (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value))
            {
                $dt =  Carbon::createFromFormat('m/d/Y', $value, $timezone)->startOfDay();
                return $dt->format($formatTime);
            }

            // If the value is in less simply year, month, day, hours, minute format, we will instantiate the
            // Carbon instances from that format. Again, this provides for simple date
            // fields on the database, while still supporting Carbonized conversion.
            elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})$/', $value))
            {
                $dt =  Carbon::createFromFormat('Y-m-d H:i', $value, $timezone);
                return $dt->format($formatTime);
            }

            // Finally, we will just assume this date is in the format used by default on
            // the database connection and use that format to create the Carbon object
            // that is returned back out to the developers after we convert it here.
            elseif ( ! $value instanceof DateTime)
            {
                $format = $this->getDateFormat();
                $dt =  Carbon::createFromFormat($format, $value, $timezone);
                return $dt->format($formatTime);
            }
            return Carbon::instance($value)->format($formatTime);
        }
    }

    /**
     * [historyFormatNumeric ]
     * @param  [type] $value
     * @return mixed     
     */
    protected function historyFormatNumeric($value)
    {
        if(!is_null($value)){
            return $value ? number_format($value, 2, ',', '.') : $value;
        }
    }

    /**
     * [formatBoolean ]
     * @param  [type] $value
     * @return mixed
     */
    protected function formatBoolean($attribute, $value)
    {
        if(!is_null($value)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $logBooleanAttributes = property_exists($this, 'logBooleanAttributes') ? $this->logBooleanAttributes : [];
            $configAttribute = $logBooleanAttributes[$attribute] ?? false;
            if($configAttribute){
                if($value) 
                    $value = $configAttribute[0] ?? $value;
                else
                    $value = $configAttribute[1] ?? $value;
            }
            return $value;
        }
    }

    /**
     * [historyFormatReference ]
     * @param  [type] $value
     * @return mixed
     */
    final protected function historyFormatReference($attribute, $value)
    {
        if(!is_null($value)) {

            if ($reference = find_reference_by_id(intval($value), false)) {

                return $reference->display_value ?? $reference->value;
            }
        }
    }

    /** 
     * [historyFormatEncryptField description]
     * @param  [type] $attribute 
     * @param  [type] $value     
     * @return mixed
     */
    final protected function historyFormatEncryptField($value)
    {
        if(!is_null($value)) {
            return ( strlen($value) < 4 ) ? $value : '******' . substr($value, -4);
        }
    }

    /** 
     * [historyFormatZipcodeField description]
     * @param  [type] $attribute 
     * @param  [type] $value     
     * @return mixed
     */
    final protected function historyFormatZipcodeField($value)
    {
        if(!is_null($value)) {

            return str_replace(\Config::get('constants.SEPERATE_STRING'), ',', $value);
        }
    }

    /** 
     * [historyFormatFileMediaField description]
     * @param  [type] $attribute 
     * @param  [type] $value     
     * @return mixed
     */
    final protected function historyFormatFileMediaField($value)
    {
        if(!is_null($value)) {

            $arrayPath = explode("/", $value);
            return end($arrayPath);
        }
    }

    /** 
     * [historyFormatCurrency description]
     * @param  [type] $attribute 
     * @param  [type] $value     
     * @return mixed
     */
    final protected function historyFormatCurrency($value)
    {
        if(!is_null($value)) {
            
            return format_dollars($value);
        }
    }

    /** 
     * [historyFormatCurrency description]
     * @param  [type] $attribute 
     * @param  [type] $value     
     * @return mixed
     */
    final protected function historyFormatPercent($value)
    {
        if(!is_null($value)) {
            
            return format_percent($value);
        }
    }

    /**
     * [historyFormatRelationShip ]
     * @param  [type] $value
     * @return mixed
     */
    final protected function historyFormatRelationShip($attribute, $value)
    {
        $configMapping = $this->relationShipAttributes[$attribute];

        if (!is_null($value) && is_array($configMapping)) {

            $element  = null;
            $value    = (int)$value;
            $mapTable = array_get($configMapping, 'mapTable');

            if (class_exists($mapTable)) {

                if (($model = (new $mapTable)) instanceof Model) {
                    $element = $model->select($configMapping['mapSelect'])
                        ->where($configMapping['mapColumn'], $value)
                        ->first();
                }
            }
            elseif($mapTable) {
                $element = DB::table($configMapping['mapTable'])
                        ->select($configMapping['mapSelect'])
                        ->where($configMapping['mapColumn'], $value)
                        ->first();
            }

            return $element ? $element->{$configMapping['mapResult']} : null;
        }
    }

    /**
     * [formatAttributeWithType ]
     * @param  [type] $attribute
     * @param  [type] $origin   
     * @param  [type] $current  
     * @return mixed
     */
    protected function formatAttributeWithType($attribute, $origin, $current):array
    {
        $columnType = $this->getColumnAttributeType($attribute);

        if (in_array($columnType, $this->typeDateTime)) 
        {
            $origin  = $this->historyFormatDateTime($origin);
            $current = $this->historyFormatDateTime($current);
        }
        elseif (in_array($columnType, $this->typeBoolean))
        {
            $current = $this->formatBoolean($attribute, $current);
            $origin  = $this->formatBoolean($attribute, $origin);
        }
        else {
            $origin  = !!$origin ? $origin : NULL;
            $current = !!$current ? $current : NULL;
        }
       
        return [ $origin, $current, $columnType ];
    }

    /** 
     * [getColumnAttributeType ]
     * @param  [type] $attribute
     * @return string
     */
    protected function getColumnAttributeType($attribute):string
    {
        return Schema::getColumnType($this->getTable(), $attribute);
    }

    /**
     * [getHistoryDisplayValueAttribute ]
     * @param  [type] $attribute
     * @param  [type] $origin   
     * @param  [type] $current  
     * @return [type]           
     */
    public function getHistoryDisplayValueAttribute($attribute, $origin, $current):array
    {
        list($origin, $current, $columnType) = $this->formatAttributeWithType($attribute, $origin, $current);

        // Check overide data
        $callback = 'getHistoryDisplayValue'. Str::studly($attribute) .'Attribute';
        
        if (method_exists($this, $callback)) {

            list($origin, $current) = call_user_func_array([$this, $callback], [ $origin, $current ]);
        }
        else {
            // Format result if the value is a relation with other table
            if ($this->relationShipAttributes[$attribute] ?? false) {
                $origin  = $this->historyFormatRelationShip($attribute, $origin);
                $current = $this->historyFormatRelationShip($attribute, $current);
            }
            // Format result if the value is a numeric.
            elseif (in_array($attribute, $this->numericAttributes)) {
                $origin  = $this->historyFormatNumeric($origin);
                $current = $this->historyFormatNumeric($current);
            }
            // As case relationship, but optimize function reference with caching.
            elseif (in_array($attribute, $this->referenceAttributes)) {
                $origin  = $this->historyFormatReference($attribute, $origin);
                $current = $this->historyFormatReference($attribute, $current);
            }
            // In case encrypt data
            elseif (in_array($attribute, $this->encryptAttributes)) {
                $origin  = $this->historyFormatEncryptField($origin);
                $current = $this->historyFormatEncryptField($current);
            }
            // In case zipcode data
            elseif (in_array($attribute, $this->zipcodeAttributes)) {
                $origin  = $this->historyFormatZipcodeField($origin);
                $current = $this->historyFormatZipcodeField($current);
            }
            // In case zipcode data
            elseif (in_array($attribute, $this->mediaAttributes)) {
                $origin  = $this->historyFormatFileMediaField($origin);
                $current = $this->historyFormatFileMediaField($current);
            }
            elseif (in_array($attribute, $this->currencyAttributes)) {
                $origin  = $this->historyFormatCurrency($origin);
                $current = $this->historyFormatCurrency($current);
            }
            elseif (in_array($attribute, $this->percentAttributes)) {
                $origin  = $this->historyFormatPercent($origin);
                $current = $this->historyFormatPercent($current);
            }
        }

        // In case Datetime
        if (!in_array($attribute, $this->ignoreFormatAttributes)) {

            $origin  = is_null($origin) ? $this->displayEmpty : $origin;
            $current = is_null($current) ? $this->displayEmpty : $current;
        }

    	return [ $origin, $current, $columnType ];
    }


    /**
     * [formatHistoryInputTag description]
     * @param  [type] $origin  
     * @param  [type] $current 
     * @return Array
     */
    protected function formatHistoryInputTag($origin, $current):array
    {
        if (!is_null($origin)) {
            $origin = str_replace(\Config::get('constants.SEPERATE_STRING'), ', ', $origin);
        }

        if (!is_null($current)) {
            $current = str_replace(\Config::get('constants.SEPERATE_STRING'), ', ', $current);
        }

        return [ $origin, $current ];
    }

    /**
     * [formatHistoryComboboxData description]
     * @param  [type] $value  
     * @param  [type] $target 
     * @return [type]         
     */
    protected function formatHistoryComboboxData($configMapping, $origin, $current)
    {
        if (!is_null($origin)) {

            $origin = implode(", ", (new $configMapping['model'])->select($configMapping['mapSelect'])
                ->whereIn($configMapping['mapColumn'], explode('@$%*', $origin))
                ->get()
                ->pluck($configMapping['mapResult'])
                ->toArray()
            );
        }

        if (!is_null($current)) {

            $current = implode(", ", (new $configMapping['model'])->select($configMapping['mapSelect'])
                ->whereIn($configMapping['mapColumn'], explode('@$%*', $current))
                ->get()
                ->pluck($configMapping['mapResult'])
                ->toArray()
            );
        }

        return [ $origin, $current ];
    }
}