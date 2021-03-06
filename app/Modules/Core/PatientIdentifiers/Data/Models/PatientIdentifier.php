<?php

namespace App\Modules\Core\PatientIdentifiers\Data\Models;

use App\Modules\Core\PatientIdentifierTypes\Data\Models\PatientIdentifierType;
use App\Modules\Core\Observations\Data\Models\Observation;
use App\Modules\Core\PatientCards\Data\Models\PatientCard;
use App\Modules\Core\Patients\Data\Models\Patient;
use App\Modules\Core\Persons\Data\Models\Person;
use Illuminate\Database\Eloquent\Model;

class PatientIdentifier extends Model
{
    //
    protected $table = 'patient_identifier';
    protected $primaryKey = 'patient_identifier_id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;

    protected $fillable = ['identifier'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function identifierType()
    {
        return $this->belongsTo(PatientIdentifierType::class, 'identifier_type');
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
        return 'patient_identifier_id';
    }

}
