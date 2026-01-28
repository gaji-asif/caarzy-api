<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChildrenAgeRange extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    protected $table  = 'children_age_ranges';
}
