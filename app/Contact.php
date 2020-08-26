<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'birthday', 'company'];

    protected $dates = ['birthday'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function path()
    {
        return url('contacts/' . $this->id);
    }
}
