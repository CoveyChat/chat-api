<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'name', 'description', 'password'
    ];

    /**
     * The primary key of the table
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function delete()
    {
        $this->permissions()->delete();
        parent::delete();
    }

    public function permissions(){
        return $this->hasMany('App\Models\Permission', 'role_id', 'id');
    }

    public function hasPermission($name, $access){
        $permission = Permission::where('role_id', $this->id)->whereHas('permissiontype', function($q) use ($name) {
            $q->where('name', $name);
        })->first();

        return $permission->$access ?? false;
    }
}