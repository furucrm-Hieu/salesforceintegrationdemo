<?php

namespace App\Helpers;
use Carbon\Carbon;

class HelperConvertDateTime 
{
  public static function convertDateTimeJpToUtc(string $string)
  {
    $dateTimeJP = Carbon::createFromFormat('Y-m-d H:i:s', $string);
		$dateTimeUTC = $dateTimeJP->subHours(9);
		return $dateTimeUTC->toDateTimeString();
  }

  public static function convertDateTimeUtcToJp(string $string)
  {
    $dateTimeUTC = Carbon::createFromFormat('Y-m-d H:i:s', $string);
		$dateTimeJP = $dateTimeUTC->addHours(9);
		return $dateTimeJP->toDateTimeString();
  }
}

