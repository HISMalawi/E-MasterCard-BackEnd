<?php

namespace App\Modules\Core\Encounters\Data\Models;

use App\Modules\Core\EncounterTypes\Data\Models\EncounterType;
use App\Modules\Core\Observations\Data\Models\Observation;
use App\Modules\Core\Patients\Data\Models\Patient;
use App\Modules\Core\Persons\Data\Models\Person;
use Illuminate\Database\Eloquent\Model;

class Encounter extends Model
{
    //
    protected $table = 'encounter';
    protected $primaryKey = 'encounter_id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;


    public function observations()
    {
        return $this->hasMany(Observation::class, 'encounter_id');
    }

    public function type()
    {
        return $this->belongsTo(EncounterType::class, 'encounter_type');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function provider()
    {
        return $this->belongsTo(Person::class, 'provider_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->uuid = uuid4();
        });
    }

    public function getRouteKey()
    {
        return 'encounter_id';
    }

}