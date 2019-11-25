<?php
namespace Bigin\History\Supports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Bigin\History\Entities\AuditHistory;

trait HistoryDetectionTrait
{	
    use HistoryValidationTrait;
    use HistoryFormatDataTypeTrait;
    use HistoryValueAttributeTrait;

    /**
     * Allow store log create
     * @var boolean
     */
    protected $isWriteLogCreated = true;

    /**
     * Allow store log create
     * @var boolean
     */
    protected $isWriteLogDeleted = true;

    /**
     * Allow store log create
     * @var boolean
     */
    protected $isWriteLogUpdated = true;

	/**
	 * [$attributeDelete description]
	 * @var array
	 */
	protected $deleteAttributes = [
		'primaryIndex' => 'id',
	];

	/**
	 * [$createAttributes description]
	 * @var array
	 */
	protected $createAttributes = [
		'primaryIndex' => 'id',
	];

	/**
	 * [$ignoreLogAttributes description]
	 * @var array
	 */
	protected $ignoreLogAttributes = [
		'updated_at',
        'updated_by'
	];

	/**
	 * [bootHistoryDetection description]
	 * Register auto detection history
	 * @author TrinhLe
	 * @return void
	 */
	protected static function bootHistoryDetectionTrait()
	{
        foreach (static::getEventListeners() as $event) {
            static::$event(function ($model) use ($event) {
                $model->createLogHistory($event);
            });
        }
	}

	/**
	 * [createLogHistory description]
	 * @param  mixed $model
	 * @author TrinhLe
	 * @return void       
	 */
	protected function createLogHistory($eventObserver)
	{
		$actionMethod = "{$eventObserver}Observer";
		if(method_exists($this, $actionMethod)) $this->$actionMethod();
	}

	/**
     * Handle the User "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function createdObserver()
    {
        if ($this->isWriteLogCreated) {

            $tableName    = $this->getHistoryDisplayTable();
            $primaryValue = $this->getAttribute($this->createAttributes['primaryIndex']);
            $fieldName    = $this->getHistoryDisplayAttribute($this->createAttributes['primaryIndex']);

            $override = array();
            if (method_exists($this, 'getContentCreateObserver')) {
                $override = $this->getContentCreateObserver();
            }
            
            $this->saveLogAttribute(array_merge([
                'type'    => \Config::get('bigin.history.history_type.log'),
                'result'  => \Config::get('bigin.history.history_result_log.fields_changed'),
                'details' => "Created new record of table {$tableName} with {$fieldName} is {$primaryValue}"
            ], $override));
        }            
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deletedObserver()
    {
        if ($this->isWriteLogDeleted) {

            $tableName    = $this->getHistoryDisplayTable();
            $primaryValue = $this->getAttribute($this->deleteAttributes['primaryIndex']);
            $fieldName    = $this->getHistoryDisplayAttribute($this->deleteAttributes['primaryIndex']);
            $override = array();

            if (method_exists($this, 'getContentdeleteObserver')) {
                $override = $this->getContentdeleteObserver();
            }
            
            $this->saveLogAttribute(array_merge([
                'type'    => \Config::get('bigin.history.history_type.log'),
                'result'  => \Config::get('bigin.history.history_result_log.fields_changed'),
                'details' => "Deleted record of table {$tableName} with {$fieldName} is {$primaryValue}"
            ], $override));
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updatedObserver()
    {
        if ($this->isWriteLogUpdated) {

            $fieldsChanged = $this->isDirty() ? $this->getDirty() : false;

            if ($fieldsChanged) {

                $fieldsChanged = $this->ignoreAttributes($fieldsChanged);

                foreach ($fieldsChanged as $attribute => $newValue) {
                    # code...

                    if( $this->getOriginal($attribute) == NULL && empty($newValue) ) 
                        continue;


                    $origin = $this->getOriginalMutator($attribute);

                    $current = $this->getNewValueMutator($attribute, $newValue);

                    list($_origin, $_current) = $this->formatAttributeWithType($attribute, $origin, $current);

                    # historyValidation model change
                    if ($this->historyValidation($_origin, $_current)) {

                        $this->createOrUpdateLogHistory($attribute, $origin, $current);
                    }
                }

            }
        }
    }

    /**
     * [getNameFieldFromValueReferenceById description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    protected function getReferenceSmart($id) 
    {
        if ($reference = find_reference_by_id((int)$id, false)) {

            return str_replace(' ', '_', strtolower($reference->value));
        }
    }

    /**
     * [ignoreAttributes description]
     * @param  mixed $fieldsChanged
     * @return array               
     */
    public function ignoreAttributes(array $fieldsChanged):array
    {
        if (!empty($this->historyOnlySpecialColumns) && is_array($this->historyOnlySpecialColumns)) {

            return array_intersect_key($fieldsChanged,  /* main array*/
                    array_flip( /* to be extracted */
                        $this->historyOnlySpecialColumns
                )
            );
        }

    	if(is_array($this->ignoreLogAttributes)){

            return array_diff_key($fieldsChanged, array_flip($this->ignoreLogAttributes));
    	}

    	return $fieldsChanged;
    }

    /**
     * [createOrUpdateLogHistory description]
     * @param  mixed $attribute
     * @param  mixed $origin   
     * @param  mixed $current  
     * @return void           
     */
    protected function createOrUpdateLogHistory($attribute, $origin, $current)
    {
        $tableName                           = $this->getHistoryDisplayTable();
        $fieldName                           = $this->getHistoryDisplayAttribute($attribute);
        list($origin, $current, $columnType) = $this->getHistoryDisplayValueAttribute($attribute, $origin, $current);
        $origin                              = is_array($origin) ? json_encode($origin) : $origin; 
        $current                             = is_array($current) ? json_encode($current) : $current;

        # GET display target update
        $targetName = NULL;
        if ($this->isDisplayHistoryUpdate ?? false) {

            $targetName = " \"" . $this->getAttribute($this->displayHistoryUpdate ?? 'id') . "\"";
        }

        $override = array();
        if (method_exists($this, 'getContentUpdateObserver')) {
            $override = $this->getContentUpdateObserver($attribute, $origin, $current) ?? [];
        }

        $this->saveLogAttribute(array_merge([
            'type'    => \Config::get('bigin.history.history_type.log'),
            'result'  => \Config::get('bigin.history.history_result_log.fields_changed'),
            'details' => "Updated {$fieldName} of {$tableName}{$targetName} from \"{$origin}\" to \"{$current}\""
        ], $override));
    }

    /**
     * [saveLogJsonAttribute description]
     * @return void
     */
    protected function saveLogAttribute(array $data = [])
    {
        $formatted = array_merge([
            'user_id'    => !empty(\Auth::user()->id) ? \Auth::user()->id : 1
        ], $this->getTargetHistory(), $data);

        AuditHistory::create($formatted);
    }

	/**
	 * [getModelEvents description]
	 * override event Observer
	 * @author TrinhLe
	 * @return array
	 */
	protected static function getEventListeners(): array
    {
    	return [
    		'updated',
    		'created',
    		'deleted'
    	];
    }
}