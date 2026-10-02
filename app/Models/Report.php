<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'facility_id', 'category', 'description', 'status', 'resolution_note', 'handled_by'];

    public function photos()
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
