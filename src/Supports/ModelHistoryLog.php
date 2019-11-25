<?php
namespace Bigin\History\Supports;

use Illuminate\Database\Eloquent\Model;

abstract class ModelHistoryLog extends Model
{
	use HistoryDetectionTrait;
}